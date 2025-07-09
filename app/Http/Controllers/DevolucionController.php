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
    $ultimoId = OrdenProduccion::max('id') + 1;
    $nueva->numero_orden = 'ORD-2025-' . str_pad($ultimoId, 3, '0', STR_PAD_LEFT);
    $nueva->estado = 'pendiente';
    $nueva->urgente = true;
    $nueva->comentarios = 'REIMPRESIÓN URGENTE POR DEVOLUCIÓN. Motivo: ' . $validated['motivo_cliente'];
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

}
