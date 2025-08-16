<?php

namespace App\View\Components\Toasts;

use App\Models\Impresion;
use Illuminate\View\Component;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AlertasImpresion extends Component
{
    public Collection $ordenesSinFin; // items: { id, numero, sig }
    public Collection $diferencias;   // items: { id, numero, solicitados, impresos, sig }

    public function __construct()
    {
        // Si agregas ?force_toasts=1 en la URL, se ignora el cache (útil para pruebas)
        $ignoreCache = request()->boolean('force_toasts');

        // (Opcional) hacerlo por usuario:
        // $uid = auth()->id();
        // $prefix = $uid ? "{$uid}:" : '';

        // --- TOASTS: FALTA FIN ---
        $this->ordenesSinFin = Impresion::with('orden')
            ->where(function ($q) {
                $q->whereNull('fin_impresion')
                  ->orWhere('fin_impresion', ''); // por si quedó vacío
            })
            ->get()
            ->map(function (Impresion $i) {
                return (object) [
                    'id'     => $i->id,
                    'numero' => optional($i->orden)->numero_orden,
                    'sig'    => $this->finSig($i),
                ];
            })
            ->filter(fn ($o) => !empty($o->numero))
            ->reject(function ($o) use ($ignoreCache/*, $prefix*/) {
                return !$ignoreCache && Cache::get("toast_cleared:{$o->sig}", false);
                // return !$ignoreCache && Cache::get("toast_cleared:{$prefix}{$o->sig}", false); // por usuario
            })
            ->unique('numero')
            ->values();

        // --- TOASTS: DIFERENCIAS DE PLIEGOS ---
        $this->diferencias = Impresion::with('orden')
            ->whereNotNull('cantidad_pliegos')
            ->whereNotNull('cantidad_pliegos_impresos')
            ->whereColumn('cantidad_pliegos', '!=', 'cantidad_pliegos_impresos')
            ->get()
            ->map(function (Impresion $i) {
                return (object) [
                    'id'          => $i->id,
                    'numero'      => optional($i->orden)->numero_orden,
                    'solicitados' => (int) $i->cantidad_pliegos,
                    'impresos'    => (int) $i->cantidad_pliegos_impresos,
                    'sig'         => $this->diffSig($i),
                ];
            })
            ->reject(function ($d) use ($ignoreCache/*, $prefix*/) {
                return !$ignoreCache && Cache::get("toast_cleared:{$d->sig}", false);
                // return !$ignoreCache && Cache::get("toast_cleared:{$prefix}{$d->sig}", false); // por usuario
            })
            ->values();
    }

    public function render()
    {
        return view('components.toasts.alertas-impresion');
    }

    // --- Helpers de firma (cambian cuando cambian los datos) ---

    private function finSig(Impresion $i): string
    {
        $ts = optional($i->updated_at)->timestamp ?? 0;
        return "fin:{$i->id}:{$ts}";
    }

    private function diffSig(Impresion $i): string
    {
        $a  = (int) $i->cantidad_pliegos;
        $b  = (int) $i->cantidad_pliegos_impresos;
        $ts = optional($i->updated_at)->timestamp ?? 0;
        return "diff:{$i->id}:{$a}-{$b}:{$ts}";
    }
}
