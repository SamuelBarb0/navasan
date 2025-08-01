<?php

namespace App\Http\Controllers;

use App\Models\Revision;
use App\Models\OrdenProduccion;
use Illuminate\Http\Request;

class RevisionController extends Controller
{
    public function index()
    {
        $revisiones = Revision::latest()->with('orden')->get();
        $ordenes = OrdenProduccion::latest()->take(20)->get();

        return view('revisiones.index', compact('revisiones', 'ordenes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'orden_id' => 'required|exists:orden_produccions,id',
            'tipo' => 'required|string',
            'comentarios' => 'nullable|string',
            'revisores' => 'required|array',
            'revisores.*.revisado_por' => 'nullable|string',
            'revisores.*.cantidad' => 'nullable|integer|min:1',
            'revisores.*.comentarios' => 'nullable|string',
        ]);

        foreach ($request->revisores as $revisor) {
            if (!empty($revisor['revisado_por']) && !empty($revisor['cantidad'])) {
                Revision::create([
                    'orden_id'     => $request->orden_id,
                    'revisado_por' => $revisor['revisado_por'],
                    'cantidad'     => $revisor['cantidad'],
                    'comentarios'  => $revisor['comentarios'] ?? null,
                    'tipo'         => $request->tipo,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Revisiones registradas correctamente.');
    }
}
