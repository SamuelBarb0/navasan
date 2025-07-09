<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Acabado;

class AcabadoController extends Controller
{
    public function index()
    {
        $acabados = Acabado::with('orden')->latest()->get();
        return view('acabados.index', compact('acabados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'orden_id' => 'required|exists:orden_produccions,id',
            'proceso' => 'required|in:laminado_mate,laminado_brillante,empalmado,suaje,corte_guillotina',
            'realizado_por' => 'required|string|max:100',
            'fecha_fin' => 'nullable|date',
        ]);

        Acabado::create([
            'orden_id' => $request->orden_id,
            'proceso' => $request->proceso,
            'realizado_por' => $request->realizado_por,
            'fecha_fin' => $request->fecha_fin, // puede ser null
        ]);

        return redirect()->back()->with('success', 'Proceso de acabado registrado.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'orden_id' => 'required|exists:orden_produccions,id',
            'proceso' => 'required|in:laminado_mate,laminado_brillante,empalmado,suaje,corte_guillotina',
            'realizado_por' => 'required|string|max:100',
            'fecha_fin' => 'nullable|date',
        ]);

        $acabado = Acabado::findOrFail($id);
        $acabado->update($request->only('orden_id', 'proceso', 'realizado_por', 'fecha_fin'));

        return redirect()->back()->with('success', 'Proceso de acabado actualizado.');
    }
}
