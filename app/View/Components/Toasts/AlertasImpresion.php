<?php

namespace App\View\Components\Toasts;

use App\Models\Impresion;
use Illuminate\View\Component;
use Illuminate\Support\Collection;

class AlertasImpresion extends Component
{
    public Collection $ordenesSinFin;
    public Collection $diferencias;

    public function __construct()
    {
        $this->ordenesSinFin = Impresion::with('orden')
            ->whereNull('fin_impresion')
            ->get()
            ->pluck('orden.numero_orden')
            ->unique();

        $this->diferencias = Impresion::with('orden')
            ->whereNotNull('cantidad_pliegos')
            ->whereNotNull('cantidad_pliegos_impresos')
            ->get()
            ->filter(fn($i) => $i->cantidad_pliegos !== $i->cantidad_pliegos_impresos);
    }

    public function render()
    {
        return view('components.toasts.alertas-impresion');
    }
}
