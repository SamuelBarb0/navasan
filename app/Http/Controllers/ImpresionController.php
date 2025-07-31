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
            'estado' => 'required|in:espera,proceso,completado,rechazado',
        ]);

        // Validación: impedir si ya existe impresión para la orden con estado diferente a 'completado'
        $impresionActiva = Impresion::where('orden_id', $request->orden_id)
            ->where('estado', '!=', 'completado')
            ->exists();

        if ($impresionActiva) {
            return redirect()->back()
                ->withErrors(['orden_id' => 'Ya existe una impresión activa (no completada) para esta orden.'])
                ->withInput();
        }

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
            'cantidad_pliegos_impresos' => 'nullable|integer|min:0',
            'inicio_impresion' => 'required|date',
            'fin_impresion' => 'required|date|after_or_equal:inicio_impresion',
            'estado' => 'required|in:espera,proceso,completado,rechazado',
        ]);

        $impresion = Impresion::findOrFail($id);
        $impresion->update($request->all());

        // Comparación lógica personalizada
        $solicitados = $request->input('cantidad_pliegos');
        $impresos   = $request->input('cantidad_pliegos_impresos');

        $mensajeExtra = null;

        if (!is_null($solicitados) && !is_null($impresos) && $impresos != $solicitados) {
            $ordenNombre = optional($impresion->orden)->numero_orden ?? 'N/A';

            if ($impresos > $solicitados) {
                $mensajeExtra = "⚠️ La cantidad de pliegos impresos de la orden #{$ordenNombre} es <strong>mayor</strong> a la cantidad solicitada.";
            } else {
                $mensajeExtra = "⚠️ La cantidad de pliegos impresos de la orden #{$ordenNombre} es <strong>menor</strong> a la cantidad solicitada.";
            }
        }

        if ($mensajeExtra) {
            return redirect()->back()
                ->with('success', 'Impresión actualizada correctamente.')
                ->with('warning_extra', $mensajeExtra);
        }

        return redirect()->back()->with('success', 'Impresión actualizada correctamente.');
    }
}
