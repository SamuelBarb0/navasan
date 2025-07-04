<?php

namespace App\Http\Controllers;

use App\Models\OrdenEtapa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdenEtapaController extends Controller
{
    public function iniciar(OrdenEtapa $etapa)
    {
        $etapa->update([
            'estado' => 'en_proceso',
            'inicio' => now(),
            'usuario_id' => Auth::id()
        ]);

        return back()->with('success', 'Etapa iniciada.');
    }

    public function finalizar(Request $request, OrdenEtapa $etapa)
    {
        $request->validate([
            'observaciones' => 'nullable|string|max:500'
        ]);

        $etapa->update([
            'estado' => 'completado',
            'fin' => now(),
            'observaciones' => $request->input('observaciones')
        ]);

        return back()->with('success', 'Etapa finalizada.');
    }
}
