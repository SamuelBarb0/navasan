<?php

namespace App\Http\Controllers;

use App\Models\OrdenEtapa;
use App\Models\OrdenProduccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdenEtapaController extends Controller
{
    public function iniciar(OrdenEtapa $etapa)
    {
        $orden = $etapa->orden;

        // Verifica si ya hay una etapa en proceso distinta
        $etapaActiva = $orden->etapas()->where('estado', 'en_proceso')->first();
        if ($etapaActiva && $etapaActiva->id !== $etapa->id) {
            return redirect()->route('ordenes.show', $orden->id)->with('error', 'Ya hay una etapa activa en proceso.');
        }

        // Inicia esta etapa
        $etapa->update([
            'estado' => 'en_proceso',
            'inicio' => now(),
            'usuario_id' => auth()->id(),
        ]);

        $orden->update([
            'etapa_actual' => $etapa->etapa->nombre,
            'estado' => 'en_proceso',
        ]);

        // 🔁 Si se inicia "Acabados", añadir las sub-etapas
        if ($etapa->etapa->nombre === 'Acabados') {
            $subEtapas = ['Laminado Mate / Brillante', 'Empalmado'];

            foreach ($subEtapas as $nombre) {
                $etapaProduccion = \App\Models\EtapaProduccion::where('nombre', $nombre)->first();

                if ($etapaProduccion && !$orden->etapas()->where('etapa_produccion_id', $etapaProduccion->id)->exists()) {
                    \App\Models\OrdenEtapa::create([
                        'orden_produccion_id' => $orden->id,
                        'etapa_produccion_id' => $etapaProduccion->id,
                        'estado' => 'pendiente',
                        'usuario_id' => $etapaProduccion->usuario_id, // asignar responsable
                    ]);
                }
            }
        }

        return redirect()->route('ordenes.show', $orden->id)->with('success', 'Etapa iniciada correctamente.');
    }



    public function finalizar(Request $request, OrdenEtapa $etapa)
    {
        $request->validate([
            'observaciones' => 'nullable|string|max:500'
        ]);

        $etapa->update([
            'estado' => 'completado',
            'fin' => now(),
            'observaciones' => $request->input('observaciones'),
        ]);

        $orden = $etapa->orden;

        // Verificar si todas las etapas están completadas
        $todasCompletadas = $orden->etapas()->where('estado', '!=', 'completado')->count() === 0;

        if ($todasCompletadas) {
            $orden->update([
                'estado' => 'concluida',
                'etapa_actual' => 'Finalizada',
            ]);
        } else {
            $orden->update([
                'etapa_actual' => $etapa->etapa->nombre,
            ]);
        }

        return redirect()->route('ordenes.show', $orden->id)->with('success', 'Etapa finalizada.');
    }
}
