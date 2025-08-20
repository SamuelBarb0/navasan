<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema; // para chequear columnas legacy
use App\Models\Laminado;
use App\Models\Suajes;
use App\Models\Empalmado;
use App\Models\OrdenProduccion;
use App\Models\ItemOrden;
use App\Models\Producto;

class AcabadoController extends Controller
{
    private const MAP = [
        'suaje-corte' => [
            'title'    => 'Procesos de Suaje y Corte',
            'model'    => Suajes::class,
            'procesos' => ['suaje', 'corte_guillotina'],
        ],
        'laminado' => [
            'title'    => 'Procesos de Laminado',
            'model'    => Laminado::class,
            'procesos' => ['laminado_mate', 'laminado_brillante'],
        ],
        'empalmado' => [
            'title'    => 'Procesos de Empalmado',
            'model'    => Empalmado::class,
            'procesos' => ['empalmado'],
        ],
    ];

    private function cfg(Request $request, ?string $tipo = null): array
    {
        if ($tipo && isset(self::MAP[$tipo])) return self::MAP[$tipo];

        $name = $request->route()?->getName() ?? '';
        foreach (self::MAP as $key => $row) {
            if (str_contains($name, $key)) return $row;
        }

        $uri = $request->path();
        foreach (self::MAP as $key => $row) {
            if (str_contains($uri, $key)) return $row;
        }

        return self::MAP['laminado'];
    }

    /**
     * Normaliza alias de campos antes de validar.
     * - cantidad_liberada  <= (cantidad_recibida | cant_recibida | recibida)
     * - cantidad_pliegos_impresos <= (cantidad_final | cant_final | final)
     * - realizado_por <= (responsable | operario | hecho_por)
     */
    private function normalizaPayload(Request $request): void
    {
        $map = [
            'cantidad_liberada'          => ['cantidad_recibida', 'cant_recibida', 'recibida'],
            'cantidad_pliegos_impresos'  => ['cantidad_final', 'cant_final', 'final'],
            'realizado_por'              => ['responsable', 'operario', 'hecho_por'],
        ];

        foreach ($map as $canon => $alts) {
            if (!$request->filled($canon)) {
                foreach ($alts as $alt) {
                    if ($request->filled($alt)) {
                        $request->merge([$canon => $request->input($alt)]);
                        break;
                    }
                }
            }
        }
    }

    public function index(Request $request, ?string $tipo = null)
    {
        $cfg   = $this->cfg($request, $tipo);
        $key   = array_search($cfg, self::MAP, true);
        $model = $cfg['model'];

        $with = ['orden.cliente'];
        $isSuaje = ($key === 'suaje-corte');
        if (!$isSuaje) $with[] = 'producto';

        $acabados = $model::with($with)->latest()->paginate(20);

        $ordenes   = OrdenProduccion::latest()->take(20)->get();
        $productos = Producto::orderBy('nombre')->take(100)->get();

        return view('acabados.index', [
            'acabados'  => $acabados,
            'titulo'    => $cfg['title'],
            'procesos'  => $cfg['procesos'],
            'ordenes'   => $ordenes,
            'productos' => $productos,
            'tipo'      => $key,
        ]);
    }

    public function store(Request $request, ?string $tipo = null)
    {
        $cfg   = $this->cfg($request, $tipo);
        $model = $cfg['model'];
        $key   = array_search($cfg, self::MAP, true);

        $this->normalizaPayload($request);

        if ($key === 'suaje-corte') {
            $validated = $request->validate([
                'orden_id'                  => 'required|exists:orden_produccions,id',
                'cantidad_liberada'         => 'required|integer|min:0',
                'cantidad_pliegos_impresos' => 'required|integer|min:0',
            ], [], [
                'cantidad_liberada'         => 'cantidad final',
                'cantidad_pliegos_impresos' => 'cantidad recibida',
            ]);

            $model::create([
                'orden_id'                  => $validated['orden_id'],
                'cantidad_liberada'         => (int) $validated['cantidad_liberada'],
                'cantidad_pliegos_impresos' => (int) $validated['cantidad_pliegos_impresos'],
            ]);

            // ALERTA de desfase (Suaje/Corte)
            $warning = $this->dispararDesfaseSuajeSiAplica(
                (int) $validated['orden_id'],
                (int) $validated['cantidad_liberada'],
                (int) $validated['cantidad_pliegos_impresos']
            );

            if ($warning) {
                return back()
                    ->with('success', 'Suaje/Corte registrado.')
                    ->with('warning_extra', $warning);
            }

            return back()->with('success', 'Suaje/Corte registrado.');
        }

        // Laminado / Empalmado
        $validated = $request->validate([
            'orden_id'                  => 'required|exists:orden_produccions,id',
            'producto_id'               => 'required|exists:productos,id',
            'proceso'                   => 'required|in:' . implode(',', $cfg['procesos']),
            'realizado_por'             => 'required|string|max:100',
            'cantidad_liberada'         => 'required|integer|min:0',   // Recibida
            'cantidad_pliegos_impresos' => 'nullable|integer|min:0',   // Final
            'fecha_fin'                 => 'nullable|date',
        ]);

        // Espejo a columnas legacy si existen (no rompe si no)
        $table  = (new $model)->getTable();
        $extra  = [];
        if (Schema::hasColumn($table, 'cantidad_libe')) {
            $extra['cantidad_libe'] = (int) $validated['cantidad_liberada'];
        }
        foreach (['responsable','operario','hecho_por'] as $legacyName) {
            if (Schema::hasColumn($table, $legacyName)) {
                $extra[$legacyName] = $validated['realizado_por'];
                break;
            }
        }

        $model::create(array_merge([
            'orden_id'                  => $validated['orden_id'],
            'producto_id'               => $validated['producto_id'],
            'proceso'                   => $validated['proceso'],
            'realizado_por'             => $validated['realizado_por'],
            'cantidad_liberada'         => (int) $validated['cantidad_liberada'],
            'cantidad_pliegos_impresos' => isset($validated['cantidad_pliegos_impresos']) ? (int) $validated['cantidad_pliegos_impresos'] : null,
            'fecha_fin'                 => $validated['fecha_fin'] ?? null,
        ], $extra));

        // ALERTA de desfase (Laminado / Empalmado)
        $tipoTitulo = ($key === 'laminado') ? 'Laminado' : 'Empalmado';
        $warning = $this->dispararDesfaseAcabadoSiAplica(
            (int) $validated['orden_id'],
            isset($validated['cantidad_pliegos_impresos']) ? (int) $validated['cantidad_pliegos_impresos'] : null, // FINAL
            (int) $validated['cantidad_liberada'], // RECIBIDA
            $tipoTitulo
        );

        if ($warning) {
            return back()
                ->with('success', 'Proceso registrado en ' . $cfg['title'] . '.')
                ->with('warning_extra', $warning);
        }

        return back()->with('success', 'Proceso registrado en ' . $cfg['title'] . '.');
    }

    public function destroy(Request $request, $id, ?string $tipo = null)
    {
        $cfg   = $this->cfg($request, $tipo);
        $model = $cfg['model'];

        $registro = $model::findOrFail($id);
        $registro->delete();

        return back()->with('success', 'Registro eliminado correctamente de ' . $cfg['title'] . '.');
    }

    public function update(Request $request, $id, ?string $tipo = null)
    {
        $cfg   = $this->cfg($request, $tipo);
        $model = $cfg['model'];
        $key   = array_search($cfg, self::MAP, true);

        $this->normalizaPayload($request);

        if ($key === 'suaje-corte') {
            $validated = $request->validate([
                'orden_id'                  => 'required|exists:orden_produccions,id',
                'cantidad_liberada'         => 'required|integer|min:0',
                'cantidad_pliegos_impresos' => 'required|integer|min:0',
            ], [], [
                'cantidad_liberada'         => 'cantidad final',
                'cantidad_pliegos_impresos' => 'cantidad recibida',
            ]);

            $registro = $model::findOrFail($id);
            $registro->update([
                'orden_id'                  => $validated['orden_id'],
                'cantidad_liberada'         => (int) $validated['cantidad_liberada'],
                'cantidad_pliegos_impresos' => (int) $validated['cantidad_pliegos_impresos'],
            ]);

            // ALERTA de desfase (Suaje/Corte)
            $warning = $this->dispararDesfaseSuajeSiAplica(
                (int) $validated['orden_id'],
                (int) $validated['cantidad_liberada'],
                (int) $validated['cantidad_pliegos_impresos']
            );

            if ($warning) {
                return back()
                    ->with('success', 'Suaje/Corte actualizado.')
                    ->with('warning_extra', $warning);
            }

            return back()->with('success', 'Suaje/Corte actualizado.');
        }

        // Laminado / Empalmado
        $validated = $request->validate([
            'orden_id'                  => 'required|exists:orden_produccions,id',
            'producto_id'               => 'required|exists:productos,id',
            'proceso'                   => 'required|in:' . implode(',', $cfg['procesos']),
            'realizado_por'             => 'required|string|max:100',
            'cantidad_liberada'         => 'required|integer|min:0',
            'cantidad_pliegos_impresos' => 'nullable|integer|min:0',
            'fecha_fin'                 => 'nullable|date',
        ]);

        $registro = $model::findOrFail($id);

        $table  = $registro->getTable();
        $extra  = [];
        if (Schema::hasColumn($table, 'cantidad_libe')) {
            $extra['cantidad_libe'] = (int) $validated['cantidad_liberada'];
        }
        foreach (['responsable','operario','hecho_por'] as $legacyName) {
            if (Schema::hasColumn($table, $legacyName)) {
                $extra[$legacyName] = $validated['realizado_por'];
                break;
            }
        }

        $registro->update(array_merge([
            'orden_id'                  => $validated['orden_id'],
            'producto_id'               => $validated['producto_id'],
            'proceso'                   => $validated['proceso'],
            'realizado_por'             => $validated['realizado_por'],
            'cantidad_liberada'         => (int) $validated['cantidad_liberada'],
            'cantidad_pliegos_impresos' => isset($validated['cantidad_pliegos_impresos']) ? (int) $validated['cantidad_pliegos_impresos'] : null,
            'fecha_fin'                 => $validated['fecha_fin'] ?? null,
        ], $extra));

        // ALERTA de desfase (Laminado / Empalmado)
        $tipoTitulo = ($key === 'laminado') ? 'Laminado' : 'Empalmado';
        $warning = $this->dispararDesfaseAcabadoSiAplica(
            (int) $validated['orden_id'],
            isset($validated['cantidad_pliegos_impresos']) ? (int) $validated['cantidad_pliegos_impresos'] : null, // FINAL
            (int) $validated['cantidad_liberada'], // RECIBIDA
            $tipoTitulo
        );

        if ($warning) {
            return back()
                ->with('success', 'Proceso actualizado en ' . $cfg['title'] . '.')
                ->with('warning_extra', $warning);
        }

        return back()->with('success', 'Proceso actualizado en ' . $cfg['title'] . '.');
    }

    public function productosPorOrden($ordenId)
    {
        $items = ItemOrden::where('orden_produccion_id', $ordenId)
            ->with(['producto:id,nombre'])
            ->select(['id', 'orden_produccion_id', 'producto_id'])
            ->orderBy('id', 'asc')
            ->get();

        $payload = $items->map(function ($it) {
            return [
                'item_id'         => $it->id,
                'producto_id'     => $it->producto_id,
                'producto_nombre' => $it->producto?->nombre,
            ];
        });

        return response()->json($payload);
    }

    private function dispararDesfaseSuajeSiAplica(int $ordenId, int $final, int $recibida): ?string
    {
        if ($recibida <= 0) return null;
        if ($final === $recibida) return null;

        $orden = OrdenProduccion::find($ordenId);
        $ordenTxt = $orden->numero_orden ?? $ordenId;

        if ($final > $recibida) {
            $msg = "⚠ <b>Desfase en Suaje</b> – Orden {$ordenTxt}: la <b>cantidad final</b> es <b>mayor</b> que la recibida ({$final} &gt; {$recibida}). Verificar.";
        } else {
            $msg = "⚠ <b>Desfase en Suaje</b> – Orden {$ordenTxt}: la <b>cantidad final</b> es <b>menor</b> que la recibida ({$final} &lt; {$recibida}). Revisar antes de despachar.";
        }

        Cache::forever('toast_suaje_desfase_global', $msg);
        return $msg;
    }

    /**
     * Desfase para Laminado / Empalmado.
     * - $final  = cantidad_pliegos_impresos (puede ser null)
     * - $recibida = cantidad_liberada
     */
    private function dispararDesfaseAcabadoSiAplica(int $ordenId, ?int $final, int $recibida, string $tipo): ?string
    {
        if (is_null($final) || $recibida <= 0) return null;
        if ($final === $recibida) return null;

        $orden = OrdenProduccion::find($ordenId);
        $ordenTxt = $orden->numero_orden ?? $ordenId;

        if ($final > $recibida) {
            $msg = "⚠ <b>Desfase en {$tipo}</b> – Orden {$ordenTxt}: la <b>cantidad final</b> es <b>mayor</b> que la recibida ({$final} &gt; {$recibida}). Verificar.";
        } else {
            $msg = "⚠ <b>Desfase en {$tipo}</b> – Orden {$ordenTxt}: la <b>cantidad final</b> es <b>menor</b> que la recibida ({$final} &lt; {$recibida}). Revisar antes de despachar.";
        }

        Cache::forever('toast_' . strtolower($tipo) . '_desfase_global', $msg);
        return $msg;
    }
}
