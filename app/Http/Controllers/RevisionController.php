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
            'revisado_por' => 'required|string|max:100',
            'cantidad' => 'required|integer|min:1',
            'tipo' => 'required|in:correcta,defectos,apartada,rechazada',
            'comentarios' => 'nullable|string|max:1000',
        ]);

        Revision::create($request->all());

        return redirect()->back()->with('success', 'Revisión registrada correctamente.');
    }
}