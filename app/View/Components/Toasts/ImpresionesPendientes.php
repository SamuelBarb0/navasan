<?php

namespace App\View\Components\Toasts;

use App\Models\Impresion;
use Illuminate\View\Component;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ImpresionesPendientes extends Component
{
    public Collection $ordenes;

    public function __construct()
    {
        // Muestra solo si la sesión lo solicita
        if (session('mostrar_toast_impresion')) {
            $this->ordenes = Impresion::with('orden')
                ->whereNull('fin_impresion')
                ->get()
                ->pluck('orden.numero_orden')
                ->filter()                   // quita null/empty
                ->unique()                   // evita duplicados
                // ⬇️ ignora órdenes que ya se limpiaron (ruta fallback guarda esta clave)
                ->reject(fn ($num) => Cache::get("toast_impresion_fin_cleared:{$num}", false))
                ->values();                  // reindexa
        } else {
            $this->ordenes = collect();
        }
    }

    public function render()
    {
        return view('components.toasts.impresiones-pendientes');
    }
}
