<?php

namespace App\Http\Controllers;

use App\Models\InventarioEtiqueta;
use App\Models\OrdenProduccion;
use Illuminate\Http\Request;
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
        $inventarios = InventarioEtiqueta::with('orden')->latest()->get();

        return view('inventario-etiquetas.index', compact('ordenes', 'inventarios'));
    }

    /**
     * Guarda un nuevo registro de etiquetas excedentes.
     */
    public function store(Request $request)
    {
        $request->validate([
            'orden_id' => 'required|exists:orden_produccions,id',
            'cantidad' => 'required|integer|min:1',
            'fecha_programada' => 'nullable|date|after_or_equal:today',
        ]);

        InventarioEtiqueta::create([
            'orden_id' => $request->orden_id,
            'cantidad' => $request->cantidad,
            'fecha_programada' => $request->fecha_programada,
        ]);

        return redirect()->back()->with('success', 'Inventario de etiquetas registrado correctamente.');
    }

    /**
     * Muestra el formulario de edición (con verificación de clave si es necesario).
     */
    public function edit($id)
    {
        $inventario = InventarioEtiqueta::findOrFail($id);
        $ordenes = OrdenProduccion::all();

        return view('inventario-etiquetas.edit', compact('inventario', 'ordenes'));
    }

    /**
     * Actualiza el inventario solo si se proporciona clave de administrador válida.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'cantidad' => 'required|numeric|min:1',
            'fecha_programada' => 'nullable|date',
            'observaciones' => 'nullable|string|max:1000',
            'admin_password' => 'required|string',
        ]);

        $admin = User::where('email', 'admin@etiquetas.com')->first(); // cambia por tu admin real

        if (!$admin || !Hash::check($request->admin_password, $admin->password)) {
            return back()->withErrors(['admin_password' => 'Contraseña de administrador incorrecta.'])->withInput();
        }

        $etiqueta = InventarioEtiqueta::findOrFail($id);
        $etiqueta->cantidad = $request->cantidad;
        $etiqueta->fecha_programada = $request->fecha_programada;
        $etiqueta->observaciones = $request->observaciones;
        $etiqueta->save();

        return redirect()->route('inventario-etiquetas.index')->with('success', 'Etiqueta actualizada correctamente.');
    }

    /**
     * Elimina el registro (también puede requerir contraseña si se desea).
     */
    public function destroy($id)
    {
        $inventario = InventarioEtiqueta::findOrFail($id);
        $inventario->delete();

        return redirect()->back()->with('success', 'Inventario eliminado correctamente.');
    }
}
