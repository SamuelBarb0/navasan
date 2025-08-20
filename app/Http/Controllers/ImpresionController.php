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
            return view('impresiones.index', compact('impresiones', 'orderes'));
        }

        // Etapa "Impresión" (sin importar asignación de usuario)
        $etapa = EtapaProduccion::where('nombre', 'Impresión')->first();

        if (!$etapa) {
            return view('impresiones.index', [
                'impresiones' => Impresion::with('orden')->latest()->get(),
                'ordenes'     => collect(),
            ]);
        }

        $etapaId     = $etapa->id;
        $ordenEtapa  = $etapa->orden;

        $ordenes = OrdenProduccion::with('cliente')
            // Debe tener la etapa de Impresión en estado trabajable
            ->whereHas('etapas', function ($q) use ($etapaId) {
                $q->where('etapa_produccion_id', $etapaId)
                    ->whereIn('estado', ['pendiente', 'en_proceso']);
            })
            // No debe tener etapas anteriores pendientes/en_proceso
            ->whereDoesntHave('etapas', function ($q) use ($ordenEtapa) {
                $q->whereIn('estado', ['pendiente', 'en_proceso'])
                    ->whereHas('etapa', function ($sub) use ($ordenEtapa) {
                        $sub->where('orden', '<', $ordenEtapa);
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
