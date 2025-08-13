<?php

namespace App\View\Components\Toasts;

use Illuminate\View\Component;
use Illuminate\Support\Collection;
use App\Models\Revision;

class RevisionPendiente extends Component
{
    public Collection $ordenes;

    public function __construct()
    {
        // Solo mostrar el toast si la sesión lo indica
        if (!session('mostrar_toast_revision')) {
            $this->ordenes = collect();
            return;
        }

        // Trae hasta 5 NÚMEROS DE ORDEN ÚNICOS, más recientes, sin nulos/vacíos
        $this->ordenes = Revision::query()
            ->select('ordenes.numero_orden')
            ->join('ordenes', 'revisions.orden_id', '=', 'ordenes.id') // ajusta si tu tabla de órdenes se llama distinto
            ->whereNotNull('ordenes.numero_orden')
            ->orderBy('revisions.created_at', 'desc')
            ->distinct()
            ->limit(5)
            ->pluck('ordenes.numero_orden')
            ->map(fn($v) => trim((string) $v))
            ->filter()      // quita vacíos
            ->values();     // reindexa
    }

    public function render()
    {
        return view('components.toasts.revision-pendiente');
    }
}
