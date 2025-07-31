<?php

namespace App\View\Components\Toasts;

use App\Models\Impresion;
use Illuminate\View\Component;
use Illuminate\Support\Collection;

class ImpresionesPendientes extends Component
{
    public Collection $ordenes;

    public function __construct()
    {
        // Solo mostrar si fue solicitado desde la sesión
        if (session('mostrar_toast_impresion')) {
            $this->ordenes = Impresion::with('orden')
                ->whereNull('fin_impresion')
                ->get()
                ->pluck('orden.numero_orden')
                ->filter()
                ->unique();
        } else {
            $this->ordenes = collect();
        }
    }

    public function render()
    {
        return view('components.toasts.impresiones-pendientes');
    }
}
