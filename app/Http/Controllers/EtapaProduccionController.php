<?php

namespace App\Http\Controllers;

use App\Models\EtapaProduccion;
use Illuminate\Http\Request;

class EtapaProduccionController extends Controller
{
    public function index()
    {
        $etapas = EtapaProduccion::orderBy('orden')->get();
        return view('etapas.index', compact('etapas'));
    }

    public function create()
    {
        return view('etapas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'     => 'required|string|max:255',
            'orden'      => 'nullable|integer',
            'usuario_id' => 'nullable|exists:users,id',
        ]);

        EtapaProduccion::create($data);

        return redirect()->route('etapas.index')->with('success', 'Etapa creada correctamente.');
    }

    public function edit(EtapaProduccion $etapa)
    {
        return view('etapas.edit', compact('etapa'));
    }

    public function update(Request $request, EtapaProduccion $etapa)
    {
        $data = $request->validate([
            'nombre'     => 'required|string|max:255',
            'orden'      => 'nullable|integer',
            'usuario_id' => 'nullable|exists:users,id',
        ]);

        $etapa->update($data);

        return redirect()->route('etapas.index')->with('success', 'Etapa actualizada correctamente.');
    }

    public function destroy(EtapaProduccion $etapa)
    {
        try {
            $etapa->delete();
            return redirect()
                ->route('etapas.index')
                ->with('success', 'Etapa eliminada correctamente.');
        } catch (\Throwable $e) {
            // Si hay FK/uso en otras tablas, mostramos un error amigable
            return redirect()
                ->route('etapas.index')
                ->with('error', 'No se pudo eliminar la etapa. Puede estar en uso en otras órdenes o procesos.');
        }
    }
}
