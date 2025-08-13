<?php

namespace App\View\Components\Toasts;

use Illuminate\View\Component;
use Illuminate\Support\Collection;
use App\Models\Revision;
use App\Models\OrdenProduccion;

class RevisionPendiente extends Component
{
    public Collection $ordenes;
    public bool $shouldShow;

    public function __construct()
    {
        // Consumir la bandera: si no existe, no mostramos.
        $this->shouldShow = (bool) session()->pull('mostrar_toast_revision', false);
        $this->ordenes = collect();

        if (!$this->shouldShow) {
            return;
        }

        $rev = (new Revision())->getTable();          // 'revisiones'
        $ord = (new OrdenProduccion())->getTable();   // p.ej. 'ordenes'

        $this->ordenes = Revision::query()
            ->join($ord, "$rev.orden_id", '=', "$ord.id")
            ->whereNotNull("$ord.numero_orden")
            ->orderBy("$rev.created_at", 'desc')
            ->distinct()
            ->limit(5)
            ->pluck("$ord.numero_orden")
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->values();

        // Si no hay nada para mostrar, anulamos el show.
        if ($this->ordenes->isEmpty()) {
            $this->shouldShow = false;
        }
    }

    public function render()
    {
        return view('components.toasts.revision-pendiente');
    }
}
