<?php

namespace App\Http\Controllers;

use App\Models\OrdenEtapa;
use App\Models\OrdenProduccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrdenEtapaController extends Controller
{
    public function iniciar(Request $request, OrdenEtapa $etapa)
    {
        $orden = $etapa->orden;
        $isDebug = (bool) config('app.debug') || $request->boolean('debug');

        Log::info('INICIAR_ETAPA::entrada', [
            'user_id'  => Auth::id(),
            'etapa_id' => $etapa->id,
            'orden_id' => $orden?->id,
            'debug'    => $isDebug,
        ]);
        $this->logEtapasEstado($orden, 'INICIAR_ETAPA::estado_inicial');

        // 1) ¿Hay otra etapa activa?
        $etapaActiva = $orden->etapas()->where('estado', 'en_proceso')->first();
        if ($etapaActiva && $etapaActiva->id !== $etapa->id) {
            Log::warning('INICIAR_ETAPA::bloqueado_por_activa', [
                'orden_id'       => $orden->id,
                'activa_id'      => $etapaActiva->id,
                'activa_nombre'  => optional($etapaActiva->etapa)->nombre,
                'solicitada_id'  => $etapa->id,
                'solicitada_nom' => optional($etapa->etapa)->nombre,
            ]);

            if ($isDebug) {
                throw new \RuntimeException(
                    "Bloqueado: ya hay una etapa activa en proceso (ID {$etapaActiva->id} - " .
                    (optional($etapaActiva->etapa)->nombre ?? 'sin nombre') . ")."
                );
            }

            return redirect()
                ->route('ordenes.show', $orden->id)
                ->with('error', 'Ya hay una etapa activa en proceso.');
        }

        // 2) ¿Hay etapas anteriores incompletas?
        $etapasOrdenadas = $orden->etapas()->with('etapa')->orderBy('id')->get();
        $indiceActual = $etapasOrdenadas->search(fn ($e) => (int)$e->id === (int)$etapa->id);

        if ($indiceActual === false) {
            Log::error('INICIAR_ETAPA::indice_no_encontrado', [
                'orden_id'  => $orden->id,
                'etapa_id'  => $etapa->id,
                'etapas_ids'=> $etapasOrdenadas->pluck('id')->all(),
            ]);
            if ($isDebug) {
                throw new \RuntimeException("No se encontró la etapa {$etapa->id} dentro de la orden {$orden->id}.");
            }
            return redirect()->route('ordenes.show', $orden->id)->with('error', 'No se encontró la etapa en la orden.');
        }

        $anteriores = $etapasOrdenadas->slice(0, $indiceActual);
        $incompletas = $anteriores->filter(fn ($e) => $e->estado !== 'completado');
        $hayAnteriorIncompleta = $incompletas->isNotEmpty();

        if ($hayAnteriorIncompleta) {
            $detalle = $incompletas->map(fn ($e) => [
                'id'     => $e->id,
                'nombre' => optional($e->etapa)->nombre,
                'estado' => $e->estado,
            ])->values()->all();

            Log::warning('INICIAR_ETAPA::bloqueado_por_anteriores', [
                'orden_id'  => $orden->id,
                'etapa_id'  => $etapa->id,
                'detalle'   => $detalle,
            ]);

            if ($isDebug) {
                $nombres = collect($detalle)->pluck('nombre')->filter()->implode(', ');
                throw new \RuntimeException(
                    'Bloqueado: hay etapas anteriores sin completar: ' . ($nombres ?: json_encode($detalle))
                );
            }

            return redirect()
                ->route('ordenes.show', $orden->id)
                ->with('error', 'Debes completar las etapas anteriores antes de iniciar esta.');
        }

        // 3) Iniciar etapa
        try {
            $etapa->update([
                'estado'     => 'en_proceso',
                'inicio'     => now(),
                'usuario_id' => auth()->id(),
            ]);

            $orden->update([
                'etapa_actual' => $etapa->etapa->nombre,
                'estado'       => 'en_proceso',
            ]);

            Log::info('INICIAR_ETAPA::ok', [
                'orden_id'  => $orden->id,
                'etapa_id'  => $etapa->id,
                'etapa_nom' => optional($etapa->etapa)->nombre,
            ]);
            $this->logEtapasEstado($orden, 'INICIAR_ETAPA::estado_final');

            return redirect()
                ->route('ordenes.show', $orden->id)
                ->with('success', 'Etapa iniciada correctamente.');
        } catch (\Throwable $e) {
            Log::error('INICIAR_ETAPA::exception', [
                'orden_id' => $orden->id,
                'etapa_id' => $etapa->id,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);
            if ($isDebug) {
                throw $e; // muestra Whoops en dev
            }
            return redirect()
                ->route('ordenes.show', $orden->id)
                ->with('error', 'Ocurrió un error al iniciar la etapa.');
        }
    }

    public function finalizar(Request $request, OrdenEtapa $etapa)
    {
        $request->validate([
            'observaciones' => 'nullable|string|max:500'
        ]);

        $orden = $etapa->orden;
        $isDebug = (bool) config('app.debug') || $request->boolean('debug');

        Log::info('FINALIZAR_ETAPA::entrada', [
            'user_id'  => Auth::id(),
            'etapa_id' => $etapa->id,
            'orden_id' => $orden?->id,
        ]);
        $this->logEtapasEstado($orden, 'FINALIZAR_ETAPA::estado_inicial');

        try {
            $etapa->update([
                'estado'        => 'completado',
                'fin'           => now(),
                'observaciones' => $request->input('observaciones'),
            ]);

            // ¿Quedan etapas sin completar?
            $quedan = $orden->etapas()->where('estado', '!=', 'completado')->exists();

            if (!$quedan) {
                $orden->update([
                    'estado'       => 'concluida',
                    'etapa_actual' => 'Finalizada',
                ]);
            } else {
                $orden->update([
                    'etapa_actual' => $etapa->etapa->nombre,
                ]);
            }

            Log::info('FINALIZAR_ETAPA::ok', [
                'orden_id' => $orden->id,
                'etapa_id' => $etapa->id,
                'quedan'   => $quedan,
            ]);
            $this->logEtapasEstado($orden, 'FINALIZAR_ETAPA::estado_final');

            return redirect()
                ->route('ordenes.show', $orden->id)
                ->with('success', 'Etapa finalizada.');
        } catch (\Throwable $e) {
            Log::error('FINALIZAR_ETAPA::exception', [
                'orden_id' => $orden->id,
                'etapa_id' => $etapa->id,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);
            if ($isDebug) {
                throw $e;
            }
            return redirect()
                ->route('ordenes.show', $orden->id)
                ->with('error', 'Ocurrió un error al finalizar la etapa.');
        }
    }

    /** Helper: loguea el estado de todas las etapas de la orden */
    private function logEtapasEstado(?OrdenProduccion $orden, string $label): void
    {
        if (!$orden) {
            Log::warning($label, ['orden' => null]);
            return;
        }
        $etapas = $orden->etapas()->with('etapa')->orderBy('id')->get()
            ->map(fn ($e) => [
                'id'     => $e->id,
                'nombre' => optional($e->etapa)->nombre,
                'estado' => $e->estado,
                'inicio' => $e->inicio?->toDateTimeString(),
                'fin'    => $e->fin?->toDateTimeString(),
            ])->all();

        Log::info($label, [
            'orden_id' => $orden->id,
            'etapas'   => $etapas,
        ]);
    }
}
