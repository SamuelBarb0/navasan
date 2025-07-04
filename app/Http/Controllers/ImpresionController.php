<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Impresion;

class ImpresionController extends Controller
{
    public function index()
    {
        $impresiones = Impresion::with('orden')->latest()->get();
        return view('impresiones.index', compact('impresiones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'orden_id' => 'required|exists:orden_produccions,id',
            'tipo_impresion' => 'required|string|max:50',
            'maquina' => 'required|string',
            'cantidad_pliegos' => 'required|integer|min:1',
            'inicio_impresion' => 'required|date',
            'fin_impresion' => 'required|date|after_or_equal:inicio_impresion',
            'estado' => 'required|in:espera,proceso,completado,rechazado',
        ]);

        Impresion::create($request->all());

        return redirect()->back()->with('success', 'Registro de impresión guardado.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'orden_id' => 'required|exists:orden_produccions,id',
            'tipo_impresion' => 'required|string|max:50',
            'maquina' => 'nullable|string|max:100',
            'cantidad_pliegos' => 'nullable|integer|min:0',
            'inicio_impresion' => 'required|date',
            'fin_impresion' => 'required|date|after_or_equal:inicio_impresion',
            'estado' => 'required|in:espera,proceso,completado,rechazado',
        ]);

        $impresion = Impresion::findOrFail($id);
        $impresion->update($request->all());

        return redirect()->back()->with('success', 'Impresión actualizada correctamente.');
    }
}
