<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Mostrar listado de clientes (opcional).
     */
    public function index()
    {
        $clientes = Cliente::all();
        return view('clientes.index', compact('clientes'));
    }

    /**
     * Registrar nuevo cliente vía AJAX (desde modal).
     */
    public function ajaxStore(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'nit' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
        ]);

        $cliente = Cliente::create($data);

        return response()->json([
            'id' => $cliente->id,
            'nombre' => $cliente->nombre,
        ]);
    }

    /**
     * Guardar cliente desde formulario tradicional (opcional).
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'nit' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
        ]);

        \App\Models\Cliente::create($request->all());

        return redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente.');
    }
}
