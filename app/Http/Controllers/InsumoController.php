<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // ✅ ESTA LÍNEA ES CLAVE
use App\Models\InsumoRecepcion;
use Illuminate\Support\Facades\Log;
use App\Models\InventarioInsumo;

class InsumoController extends Controller
{
    public function index()
    {
        $insumos = Insumo::orderBy('nombre')->get();
        return view('insumos.index', compact('insumos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'unidad' => 'required|string|max:50',
            'cantidad' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $insumo = \App\Models\Insumo::create([
                'nombre' => $request->nombre,
                'unidad' => $request->unidad,
                'activo' => true,
            ]);

            \App\Models\InventarioInsumo::create([
                'insumo_id' => $insumo->id,
                'cantidad_disponible' => $request->cantidad,
            ]);

            DB::commit();
            return redirect()->route('insumos.index')->with('success', 'Insumo creado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear el insumo: ' . $e->getMessage());
        }
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'unidad' => 'required|string|max:50',
            'descripcion' => 'nullable|string',
            'cantidad_actual' => 'nullable|numeric|min:0',
            'activo' => 'required|boolean',
        ]);

        $insumo = Insumo::findOrFail($id);
        $insumo->update($request->only(['nombre', 'unidad', 'descripcion', 'activo']));

        // Actualizar inventario
        $inventario = \App\Models\InventarioInsumo::firstOrCreate(
            ['insumo_id' => $insumo->id],
            ['cantidad_disponible' => 0]
        );

        $inventario->cantidad_disponible = $request->cantidad_actual;
        $inventario->save();

        return redirect()->route('insumos.index')->with('success', 'Insumo actualizado correctamente.');
    }

public function storeRecepcion(Request $request)
{
    \Log::info('Iniciando recepción de insumo', $request->all());

    $request->validate([
        'insumo_id'        => 'required|exists:insumos,id',
        'cantidad_recibida'=> 'required|numeric|min:0.01',
        'tipo_recepcion'   => 'required|string|max:255',
        'fecha_recepcion'  => 'nullable|date',
        'factura_archivo'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
    ]);

    try {
        $insumo = Insumo::findOrFail($request->insumo_id);

        // Subir archivo (si aplica)
        $rutaArchivo = null;
        if ($request->hasFile('factura_archivo')) {
            $rutaArchivo = $request->file('factura_archivo')->store('recepciones', 'public');
        }

        // Registrar la recepción
        InsumoRecepcion::create([
            'insumo_id'         => $insumo->id,
            'cantidad_recibida' => $request->cantidad_recibida,
            'tipo_recepcion'    => $request->tipo_recepcion,
            'fecha_recepcion'   => $request->fecha_recepcion,
            'archivo_factura'   => $rutaArchivo,
        ]);

        // Buscar o crear el inventario
        $inventario = InventarioInsumo::where('insumo_id', $insumo->id)->first();

        if (!$inventario) {
            $inventario = InventarioInsumo::create([
                'insumo_id' => $insumo->id,
                'cantidad_disponible' => 0,
            ]);
        }

        // Actualizar cantidad
        $inventario->cantidad_disponible += $request->cantidad_recibida;
        $inventario->save();

        \Log::info('Recepción registrada correctamente para insumo ID ' . $insumo->id);

        return redirect()->back()->with('success', 'Recepción registrada correctamente.');

    } catch (\Exception $e) {
        \Log::error('Error en recepción de insumo: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Ocurrió un error al registrar la recepción.');
    }
}
}
