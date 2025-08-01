<?php

namespace App\Http\Controllers;

use App\Models\InventarioEtiqueta;
use App\Models\OrdenProduccion;
use App\Models\ItemOrden;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class InventarioEtiquetaController extends Controller
{
    /**
     * Muestra el formulario y lista de inventario.
     */
    public function index()
    {
        $ordenes = OrdenProduccion::orderBy('created_at', 'desc')->get();
        $inventarios = InventarioEtiqueta::with(['orden', 'itemOrden', 'producto'])->latest()->get();
        $productos = Producto::orderBy('nombre')->get();

        return view('inventario-etiquetas.index', compact('ordenes', 'inventarios', 'productos'));
    }

    /**
     * Guarda un nuevo registro de etiquetas excedentes.
     */
    public function store(Request $request)
    {
        Log::debug('📥 Datos recibidos para guardar etiqueta:', $request->all());

        $validated = $request->validate([
            'orden_id'         => 'nullable|exists:orden_produccions,id',
            'item_orden_id'    => 'nullable|required_with:orden_id|exists:item_ordens,id',
            'producto_id'      => 'nullable|required_without:orden_id|exists:productos,id',
            'cantidad'         => 'required|integer|min:1',
            'fecha_programada' => 'nullable|date|after_or_equal:today',
            'observaciones'    => 'nullable|string|max:1000',
        ]);

        try {
            $etiqueta = InventarioEtiqueta::create([
                'orden_id'         => $request->orden_id ?: null,
                'item_orden_id'    => $request->orden_id ? $request->item_orden_id : null,
                'producto_id'      => $request->orden_id ? null : $request->producto_id,
                'cantidad'         => $request->cantidad,
                'fecha_programada' => $request->fecha_programada,
                'observaciones'    => $request->observaciones,
                'estado'           => 'pendiente',
                'alertado'         => false,
            ]);

            Log::debug('✅ Etiqueta guardada correctamente:', $etiqueta->toArray());

            return redirect()->back()->with('success', 'Inventario de etiquetas registrado correctamente.');
        } catch (\Throwable $e) {
            Log::error('❌ Error al guardar etiqueta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo guardar la etiqueta. Revisa los datos e intenta de nuevo.');
        }
    }

    /**
     * Muestra el formulario de edición.
     */
    public function edit($id)
    {
        $inventario = InventarioEtiqueta::findOrFail($id);
        $ordenes = OrdenProduccion::all();
        $productos = Producto::all();

        return view('inventario-etiquetas.edit', compact('inventario', 'ordenes', 'productos'));
    }

    public function update(Request $request, $id)
    {
        Log::debug('🔧 Iniciando actualización de etiqueta', ['etiqueta_id' => $id]);
        Log::debug('📥 Datos recibidos para actualizar:', $request->all());

        // Validar que el usuario tenga el rol 'etiquetador' o 'administrador'
        if (!auth()->user()->hasAnyRole(['etiquetador', 'administrador'])) {
            Log::warning('🚫 Usuario sin rol autorizado intentó actualizar', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
            ]);

            return redirect()->back()->with('error', 'No tienes permiso para actualizar etiquetas.');
        }

        $request->validate([
            'orden_id'         => 'nullable|exists:orden_produccions,id',
            'item_orden_id'    => 'nullable|required_with:orden_id|exists:item_ordens,id',
            'producto_id'      => 'nullable|required_without:orden_id|exists:productos,id',
            'cantidad'         => 'required|numeric|min:1',
            'fecha_programada' => 'nullable|date',
            'observaciones'    => 'nullable|string|max:1000',
            'estado'           => 'required|in:pendiente,liberado,stock',
        ]);

        $etiqueta = InventarioEtiqueta::findOrFail($id);

        $etiqueta->orden_id         = $request->orden_id ?: null;
        $etiqueta->item_orden_id    = $request->orden_id ? $request->item_orden_id : null;
        $etiqueta->producto_id      = $request->orden_id ? null : $request->producto_id;
        $etiqueta->cantidad         = $request->cantidad;
        $etiqueta->fecha_programada = $request->fecha_programada;
        $etiqueta->observaciones    = $request->observaciones;
        $etiqueta->estado           = $request->estado;

        $etiqueta->save();

        Log::info('✅ Etiqueta actualizada correctamente', [
            'id' => $etiqueta->id,
            'orden_id' => $etiqueta->orden_id,
            'item_orden_id' => $etiqueta->item_orden_id,
            'producto_id' => $etiqueta->producto_id,
        ]);

        return redirect()->route('inventario-etiquetas.index')->with('success', 'Etiqueta actualizada correctamente.');
    }

    /**
     * Elimina el registro de inventario.
     */
    public function destroy($id)
    {
        $inventario = InventarioEtiqueta::findOrFail($id);
        $inventario->delete();

        return redirect()->back()->with('success', 'Inventario eliminado correctamente.');
    }
}
