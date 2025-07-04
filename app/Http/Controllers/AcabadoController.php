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
        ]);

        Acabado::create($request->all());

        return redirect()->back()->with('success', 'Proceso de acabado registrado.');
    }
}
