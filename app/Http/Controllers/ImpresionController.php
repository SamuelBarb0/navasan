<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Impresion;
use App\Models\EtapaProduccion;
use App\Models\OrdenProduccion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        // 4) (Opcional) inspección de bloqueos para una orden que esperes ver
        // Reemplaza 1234 por una que no esté apareciendo
        /*
    $ordenId = 1234;
    $bloqueos = DB::table('orden_etapas as oe')
        ->join('etapa_produccions as ep', 'ep.id', '=', 'oe.etapa_produccion_id')
        ->where('oe.orden_produccion_id', $ordenId)
        ->where('ep.orden', '<', $ordenEtapa)
        ->whereIn(DB::raw('LOWER(oe.estado)'), ['pendiente','en_proceso'])
        ->get(['oe.id','oe.estado','ep.nombre','ep.orden']);
    Log::info('[Impresiones] Bloqueos previos', $bloqueos->toArray());
    */

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

        Impresion::create($request->all());

        return redirect()->back()->with('success', 'Registro de impresión guardado.');
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

        if ($mensajeExtra) {
            return redirect()->back()
                ->with('success', 'Impresión actualizada correctamente.')
                ->with('warning_extra', $mensajeExtra);
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
}
