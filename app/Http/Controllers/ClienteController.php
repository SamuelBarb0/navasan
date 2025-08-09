<?php
namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::all();
        return view('clientes.index', compact('clientes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'nit' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
        ]);

        Cliente::create($data);

        return redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente.');
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


    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'nit' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:255',
        ]);

        $cliente->update($data);

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado correctamente.');
    }
}
