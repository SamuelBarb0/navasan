<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Impresion;
use App\Models\EtapaProduccion;
use App\Models\OrdenProduccion;
use Illuminate\Support\Facades\DB;

class ImpresionController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();

        // 👑 Administrador: ver todo
        if ($usuario->hasRole('administrador')) {
            $impresiones = Impresion::with('orden')->latest()->get();
            $ordenes = OrdenProduccion::latest()->take(20)->get();
            return view('impresiones.index', compact('impresiones', 'ordenes'));
        }

        // 🧑‍🔧 Responsable: buscar etapa "Impresión" asignada a él
        $etapa = EtapaProduccion::where('usuario_id', $usuario->id)
            ->where('nombre', 'Impresión')
            ->first();

        if (!$etapa) {
            return view('impresiones.index', [
                'impresiones' => Impresion::with('orden')->latest()->get(),
                'ordenes' => collect(),
            ]);
        }

        $etapaId    = $etapa->id;
        $ordenEtapa = $etapa->orden;

        $ordenes = OrdenProduccion::with('cliente')
            ->whereHas('etapas', function ($q) use ($usuario, $etapaId, $ordenEtapa) {
                $q->where('etapa_produccion_id', $etapaId)
                    ->where('usuario_id', $usuario->id)
                    ->whereIn('estado', ['pendiente', 'en_proceso'])
                    ->whereNotExists(function ($subquery) use ($ordenEtapa) {
                        $subquery->select(DB::raw(1))
                            ->from('orden_etapas as anteriores')
                            ->join('etapa_produccions as ep', 'anteriores.etapa_produccion_id', '=', 'ep.id')
                            ->whereColumn('anteriores.orden_produccion_id', 'orden_etapas.orden_produccion_id')
                            ->where('ep.orden', '<', $ordenEtapa)
                            ->whereIn('anteriores.estado', ['pendiente', 'en_proceso']);
                    });
            })
            ->latest()
            ->take(20)
            ->get();

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
