<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Devolucion;
use App\Models\OrdenProduccion;

class DevolucionController extends Controller
{
    public function index()
    {
        $devoluciones = Devolucion::with('orden.cliente')->latest()->get();
        $ordenes = OrdenProduccion::with('cliente')->get();
        return view('devoluciones.index', compact('devoluciones', 'ordenes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'orden_id' => 'required|exists:orden_produccions,id',
            'motivo_cliente' => 'required|string',
            'revisadora_asignada' => 'required|string',
            'tipo_error' => 'nullable|string',
            'codigo_rojo' => 'nullable|boolean',
            'comentarios_adicionales' => 'nullable|string',
        ]);

        $validated['codigo_rojo'] = $request->has('codigo_rojo');

        // Crear la devolución
        $devolucion = Devolucion::create($validated);

        // Cargar orden original con todas sus relaciones
        $original = OrdenProduccion::with([
            'impresiones',
            'insumos',
            'acabados',
            'items.entregas', // Aseguramos que cargue las entregas
            'etapas',
        ])->findOrFail($validated['orden_id']);

        // Clonar orden
        $nueva = $original->replicate();

        // Limpiar el número original "por si acaso" y prefijar URG-
        $numeroLimpio = preg_replace('/[\/\\\\:*?"<>|]/', '-', (string) $original->numero_orden);
        $numeroLimpio = trim(preg_replace('/-+/', '-', $numeroLimpio), '- ');
        $nueva->numero_orden = 'URG-' . $numeroLimpio;

        $nueva->estado = 'pendiente';
        $nueva->urgente = true;
        $nueva->comentarios = $validated['motivo_cliente'];
        $nueva->save();

        // Clonar relaciones
        foreach ($original->impresiones as $impresion) {
            $nueva->impresiones()->create($impresion->toArray());
        }

        foreach ($original->insumos as $insumo) {
            $nuevoInsumo = $insumo->replicate();
            $nuevoInsumo->orden_produccion_id = $nueva->id;
            $nuevoInsumo->save();
        }

        foreach ($original->acabados as $acabado) {
            $nueva->acabados()->create($acabado->toArray());
        }

        foreach ($original->items as $item) {
            $nuevoItem = $item->replicate();
            $nuevoItem->orden_produccion_id = $nueva->id;
            $nuevoItem->save();

            // Clonar entregas del item
            foreach ($item->entregas as $entrega) {
                $nuevaEntrega = $entrega->replicate();
                $nuevaEntrega->item_orden_id = $nuevoItem->id;
                $nuevaEntrega->save();
            }
        }

        foreach ($original->etapas as $etapa) {
            $nuevaEtapa = $etapa->replicate();
            $nuevaEtapa->orden_produccion_id = $nueva->id;
            $nuevaEtapa->save();
        }

        return redirect()->route('devoluciones.index')->with('success', 'Devolución registrada y orden urgente generada.');
    }

    public function revisionesPorOrden(OrdenProduccion $orden)
    {
        // Cargamos revisiones reales según tu esquema actual
        $revisiones = $orden->revisiones()
            ->latest()
            ->get(['id', 'revisado_por', 'cantidad', 'tipo', 'comentarios', 'created_at']);

        // Lista única de "usuarios" basada en el campo string revisado_por
        $usuarios = $revisiones->pluck('revisado_por')
            ->filter()
            ->unique()
            ->values()
            ->map(fn($name) => ['name' => $name]);

        // Devolvemos en el formato que espera tu JS (resultado/observaciones)
        return response()->json([
            'revisiones' => $revisiones->map(function ($r) {
                return [
                    'id'            => $r->id,
                    'usuario'       => $r->revisado_por,              // nombre de quien revisó
                    'resultado'     => $r->tipo,                      // mapeo a "resultado"
                    'observaciones' => $r->comentarios,               // mapeo a "observaciones"
                    'cantidad'      => $r->cantidad,                  // por si lo usas luego
                    'created_at'    => optional($r->created_at)->format('Y-m-d H:i'),
                ];
            }),
            'usuarios' => $usuarios,
        ]);
    }

    public function destroy(Devolucion $devolucion)
    {
        // (Opcional) Si quieres restringir por roles:
        // if (!auth()->user()->hasRole('administrador')) {
        //     return back()->withErrors(['permiso' => 'No tienes permisos para eliminar devoluciones.']);
        // }

        try {
            $devolucion->delete();
            return back()->with('success', 'Devolución eliminada correctamente.');
        } catch (\Throwable $e) {
            return back()->withErrors(['delete' => 'No se pudo eliminar la devolución.']);
        }
    }
}
