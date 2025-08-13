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
        // Consumir bandera: solo una vez
        $this->shouldShow = (bool) session()->pull('mostrar_toast_revision', false);
        $this->ordenes = collect();

        if (!$this->shouldShow) {
            return;
        }

        $rev = (new Revision())->getTable();          // 'revisiones'
        $ord = (new OrdenProduccion())->getTable();   // tabla real de OrdenProduccion

        // Traemos un "pool" generoso (p.ej. 50) y luego nos quedamos con los 5 únicos más recientes
        $rows = Revision::query()
            ->join($ord, "$rev.orden_id", '=', "$ord.id")
            ->whereNotNull("$ord.numero_orden")
            ->orderBy("$rev.created_at", 'desc')
            ->limit(50)
            ->get(["$ord.numero_orden"]);

        $this->ordenes = $rows
            ->pluck('numero_orden')
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->take(5)
            ->values();

        if ($this->ordenes->isEmpty()) {
            $this->shouldShow = false;
        }
    }

    public function render()
    {
        return view('components.toasts.revision-pendiente');
    }
}
