<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Impresion;
use App\Models\EtapaProduccion;
use App\Models\OrdenProduccion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ImpresionController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();

        if ($usuario->hasRole('administrador')) {
            $impresiones = Impresion::with('orden')->latest()->get();
            $ordenes = OrdenProduccion::latest()->take(20)->get();
            return view('impresiones.index', compact('impresiones', 'ordenes'));
        }

        // 1) Buscar etapa "Impresión" tolerando tilde/no tilde
        $etapa = EtapaProduccion::whereIn('nombre', ['Impresión', 'Impresion'])->first();

        if (!$etapa) {
            Log::warning('[Impresiones] No existe etapa Impresión/Impresion en BD');
            return view('impresiones.index', [
                'impresiones' => Impresion::with('orden')->latest()->get(),
                'ordenes'     => collect(),
            ]);
        }

        $etapaId    = $etapa->id;
        $ordenEtapa = $etapa->orden;

        // 2) Query tolerante a mayúsculas en estado
        $ordenes = OrdenProduccion::with('cliente')
            // Debe tener la etapa de Impresión en estado trabajable
            ->whereHas('etapas', function ($q) use ($etapaId) {
                $q->where('etapa_produccion_id', $etapaId)
                    ->whereIn(DB::raw('LOWER(estado)'), ['pendiente', 'en_proceso']);
            })
            // No debe tener etapas anteriores pendientes o en_proceso
            ->whereDoesntHave('etapas', function ($q) use ($ordenEtapa) {
                $q->whereIn(DB::raw('LOWER(estado)'), ['pendiente', 'en_proceso'])
                    ->whereHas('etapa', function ($sub) use ($ordenEtapa) {
                        $sub->where('orden', '<', $ordenEtapa);
                    });
            })
            ->latest()
            ->take(20)
            ->get(['id', 'numero_orden', 'cliente_id', 'created_at']);

        // 3) Log de debug: cuántas y cuáles IDs trae
        Log::info('[Impresiones] Ordenes para etapa Impresión', [
            'count' => $ordenes->count(),
            'ids'   => $ordenes->pluck('id')->all(),
        ]);

        $impresiones = Impresion::with('orden')->latest()->get();

        return view('impresiones.index', compact('impresiones', 'ordenes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'orden_id'          => 'required|exists:orden_produccions,id',
            'tipo_impresion'    => 'required|string|max:50',
            'maquina'           => 'required|string',
            'cantidad_pliegos'  => 'required|integer|min:1',
            'inicio_impresion'  => 'required|date',
            'estado'            => 'required|in:espera,proceso,completado,rechazado',
        ]);

        // Impedir crear si ya hay impresión activa (no completada)
        $impresionActiva = Impresion::where('orden_id', $request->orden_id)
            ->where('estado', '!=', 'completado')
            ->exists();

        if ($impresionActiva) {
            return redirect()->back()
                ->withErrors(['orden_id' => 'Ya existe una impresión activa (no completada) para esta orden.'])
                ->withInput();
        }

        // Crear e inmediatamente evaluar avisos globales (misma lógica que Acabados)
        $imp = Impresion::create($request->all());

        $warnDesfase = $this->dispararDesfaseImpresionSiAplica($imp);
        $warnFaltaFin = $this->dispararFaltaFinImpresionSiAplica($imp);

        return redirect()->back()
            ->with('success', 'Registro de impresión guardado.')
            ->with('warning_extra', $warnDesfase ?? $warnFaltaFin);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'orden_id'                   => 'required|exists:orden_produccions,id',
            'tipo_impresion'             => 'required|string|max:50',
            'maquina'                    => 'nullable|string|max:100',
            'cantidad_pliegos'           => 'nullable|integer|min:0',
            'cantidad_pliegos_impresos'  => 'nullable|integer|min:0',
            'inicio_impresion'           => 'required|date',
            'fin_impresion'              => 'required|date|after_or_equal:inicio_impresion',
            'estado'                     => 'required|in:espera,proceso,completado,rechazado',
        ]);

        // Traemos con la relación para usar el número de orden en el mensaje
        $impresion = Impresion::with('orden')->findOrFail($id);

        // Guardamos cambios
        $impresion->update($request->all());

        // ⚠️ Comparación usando los valores YA persistidos en el modelo
        $solicitados = $impresion->cantidad_pliegos;            // solicitados/planificados
        $impresos    = $impresion->cantidad_pliegos_impresos;   // realmente impresos

        $mensajeExtra = null;

        if ($solicitados !== null && $impresos !== null && (int)$impresos !== (int)$solicitados) {
            $ordenNombre = optional($impresion->orden)->numero_orden ?? 'N/A';

            if ((int)$impresos > (int)$solicitados) {
                $mensajeExtra = "⚠️ La cantidad de pliegos impresos de la orden #{$ordenNombre} es <strong>mayor</strong> a la cantidad solicitada.";
            } else {
                $mensajeExtra = "⚠️ La cantidad de pliegos impresos de la orden #{$ordenNombre} es <strong>menor</strong> a la cantidad solicitada.";
            }
        }

        // Disparar toasts globales como en Acabados
        $warnDesfase = $this->dispararDesfaseImpresionSiAplica($impresion);
        $warnFaltaFin = $this->dispararFaltaFinImpresionSiAplica($impresion);

        if ($mensajeExtra || $warnDesfase || $warnFaltaFin) {
            $combined = collect([$mensajeExtra, $warnDesfase, $warnFaltaFin])
                ->filter()
                ->implode('<br>');
            return redirect()->back()
                ->with('success', 'Impresión actualizada correctamente.')
                ->with('warning_extra', $combined);
        }

        return redirect()->back()->with('success', 'Impresión actualizada correctamente.');
    }

    public function destroy($id)
    {
        $impresion = Impresion::findOrFail($id);

        try {
            $impresion->delete();
            return redirect()->back()->with('success', 'Registro de impresión eliminado.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors([
                'delete' => 'No se pudo eliminar el registro.'
            ]);
        }
    }

    /**
     * Desfase global para Impresión (mismo patrón que Acabados):
     * Compara cantidad solicitada (cantidad_pliegos) vs cantidad impresa (cantidad_pliegos_impresos).
     * Si hay diferencia, setea Cache::forever('toast_impresion_desfase_global', $msg)
     */
    private function dispararDesfaseImpresionSiAplica(Impresion $imp): ?string
    {
        // Planificado vs Real
        $solicitados = is_null($imp->cantidad_pliegos) ? null : (int) $imp->cantidad_pliegos;
        $impresos    = is_null($imp->cantidad_pliegos_impresos) ? null : (int) $imp->cantidad_pliegos_impresos;

        if ($solicitados === null || $impresos === null) return null;
        if ($solicitados === $impresos) return null;

        $ordenTxt = optional($imp->orden)->numero_orden ?? $imp->orden_id ?? $imp->id;

        $msg = $impresos > $solicitados
            ? "⚠ <b>Desfase en Impresión</b> – Orden {$ordenTxt}: la <b>cantidad impresa</b> es <b>mayor</b> que la solicitada ({$impresos} &gt; {$solicitados}). Verificar."
            : "⚠ <b>Desfase en Impresión</b> – Orden {$ordenTxt}: la <b>cantidad impresa</b> es <b>menor</b> que la solicitada ({$impresos} &lt; {$solicitados}). Revisar antes de continuar.";

        Cache::forever('toast_impresion_desfase_global', $msg);
        return $msg;
    }

    /**
     * Aviso global por falta de fin en Impresión (mismo patrón que Acabados):
     * Si no hay fin_impresion, setea Cache::forever('toast_impresion_global', $msg)
     */
    private function dispararFaltaFinImpresionSiAplica(Impresion $imp): ?string
    {
        // Criterio simple: siempre que no tenga fin
        if (!empty($imp->fin_impresion)) return null;

        $ordenTxt = optional($imp->orden)->numero_orden ?? $imp->orden_id ?? $imp->id;
        $msg = "⚠ <b>Aviso de Impresión</b> – Orden {$ordenTxt}: falta registrar la <b>fecha de fin de impresión</b>.";

        Cache::forever('toast_impresion_global', $msg);
        return $msg;
    }
}
