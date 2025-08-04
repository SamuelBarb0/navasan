<?php

namespace App\View\Components\Toasts;

use App\Models\Revision;
use Illuminate\View\Component;
use Illuminate\Support\Collection;

class RevisionPendiente extends Component
{
    public Collection $ordenes;

    public function __construct()
    {
        // Solo mostrar el toast si la sesión lo indica
        if (session('mostrar_toast_revision')) {
            $this->ordenes = Revision::with('orden')
                ->latest()
                ->take(5)
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
        return view('components.toasts.revision-pendiente');
    }
}
