<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InsumoOrden;
use App\Models\Insumo;
use App\Models\InventarioInsumo;

class InsumoOrdenController extends Controller
{
    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,liberado,solicitado',
        ]);

        $insumoOrden = InsumoOrden::findOrFail($id);
        $insumoOrden->estado = $request->estado;
        $insumoOrden->save();

        return redirect()->back()->with('success', 'Estado del insumo actualizado.');
    }

    public function store(Request $request, $ordenId)
    {
        $request->validate([
            'insumo_id' => 'required|exists:insumos,id',
            'cantidad_requerida' => 'required|numeric|min:1',
            'cantidad_recibida' => 'nullable|numeric|min:0',
            'tipo_recepcion' => 'nullable|string|max:50',
            'fecha_recepcion' => 'nullable|date',
            'factura_archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $insumoId = $request->insumo_id;
        $cantidad = $request->cantidad_requerida;
        $estado = 'pendiente';

        // Verificamos inventario
        $inventario = InventarioInsumo::where('insumo_id', $insumoId)->first();
        if ($inventario && $inventario->cantidad_disponible >= $cantidad) {
            $estado = 'liberado';
            $inventario->cantidad_disponible -= $cantidad;
            $inventario->save();
        } else {
            $estado = 'solicitado';
        }

        $data = $request->only([
            'insumo_id',
            'cantidad_requerida',
            'cantidad_recibida',
            'tipo_recepcion',
            'fecha_recepcion',
        ]);

        $data['orden_produccion_id'] = $ordenId;
        $data['estado'] = $estado;

        if ($request->hasFile('factura_archivo')) {
            $data['factura_archivo'] = $request->file('factura_archivo')->store('facturas', 'public');
        }

        InsumoOrden::create($data);

        return back()->with('success', 'Insumo agregado correctamente.');
    }

    public function storeDesdeOrden(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'unidad' => 'nullable|string|max:50',
            'orden_id' => 'required|exists:orden_produccions,id',
            'cantidad_requerida' => 'required|numeric|min:1',
            'cantidad_recibida' => 'nullable|numeric|min:0',
            'tipo_recepcion' => 'nullable|string|max:255',
            'fecha_recepcion' => 'nullable|date',
            'factura_archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // 1. Crear el insumo
        $insumo = Insumo::create($request->only('nombre', 'unidad'));

        // 2. Verificar inventario
        $estado = 'pendiente';
        $cantidad = $request->cantidad_requerida;
        $inventario = InventarioInsumo::where('insumo_id', $insumo->id)->first();

        if ($inventario && $inventario->cantidad_disponible >= $cantidad) {
            $estado = 'liberado';
            $inventario->cantidad_disponible -= $cantidad;
            $inventario->save();
        } else {
            $estado = 'solicitado';
        }

        // 3. Archivo
        $archivoPath = null;
        if ($request->hasFile('factura_archivo')) {
            $archivoPath = $request->file('factura_archivo')->store('facturas', 'public');
        }

        // 4. Crear insumo_orden
        InsumoOrden::create([
            'orden_produccion_id' => $request->orden_id,
            'insumo_id' => $insumo->id,
            'cantidad_requerida' => $cantidad,
            'cantidad_recibida' => $request->cantidad_recibida,
            'tipo_recepcion' => $request->tipo_recepcion,
            'fecha_recepcion' => $request->fecha_recepcion,
            'factura_archivo' => $archivoPath,
            'estado' => $estado,
        ]);

        return back()->with('success', 'Insumo y recepción agregados correctamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'cantidad_requerida' => 'required|numeric|min:1',
            'cantidad_recibida' => 'nullable|numeric|min:0',
            'tipo_recepcion' => 'nullable|string|max:50',
            'fecha_recepcion' => 'nullable|date',
            'factura_archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $insumoOrden = InsumoOrden::findOrFail($id);
        $insumoId = $insumoOrden->insumo_id;

        $inventario = \App\Models\InventarioInsumo::where('insumo_id', $insumoId)->first();
        $nuevaCantidad = $request->cantidad_requerida;

        // Si se cambia la cantidad, se debe devolver la anterior al inventario
        if ($inventario) {
            // Devolver la anterior (si estaba liberado)
            if ($insumoOrden->estado === 'liberado') {
                $inventario->cantidad_disponible += $insumoOrden->cantidad_requerida;
            }

            // Verificar si ahora alcanza
            if ($inventario->cantidad_disponible >= $nuevaCantidad) {
                $insumoOrden->estado = 'liberado';
                $inventario->cantidad_disponible -= $nuevaCantidad;
            } else {
                $insumoOrden->estado = 'solicitado';
            }

            $inventario->save();
        } else {
            $insumoOrden->estado = 'solicitado';
        }

        $insumoOrden->cantidad_requerida = $nuevaCantidad;
        $insumoOrden->cantidad_recibida = $request->cantidad_recibida;
        $insumoOrden->tipo_recepcion = $request->tipo_recepcion;
        $insumoOrden->fecha_recepcion = $request->fecha_recepcion;

        if ($request->hasFile('factura_archivo')) {
            $insumoOrden->factura_archivo = $request->file('factura_archivo')->store('facturas', 'public');
        }

        $insumoOrden->save();

        return back()->with('success', 'Insumo actualizado correctamente.');
    }
}
