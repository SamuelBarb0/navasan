<?php

namespace App\View\Components\Toasts;

use Illuminate\View\Component;
use Illuminate\Support\Collection;
use App\Models\Revision;
use App\Models\OrdenProduccion;

class RevisionPendiente extends Component
{
    public Collection $ordenes;

    public function __construct()
    {
        $rev = (new Revision())->getTable();          // 'revisiones'
        $ord = (new OrdenProduccion())->getTable();   // tabla real

        // Trae números de orden únicos (máx 5), recientes, no nulos/ni vacíos
        $rows = Revision::query()
            ->join($ord, "$rev.orden_id", '=', "$ord.id")
            ->whereNotNull("$ord.numero_orden")
            ->orderBy("$rev.created_at", 'desc')
            ->limit(50)
            ->get(["$ord.numero_orden"]);

        $this->ordenes = $rows->pluck('numero_orden')
            ->map(fn($v) => trim((string)$v))
            ->filter()
            ->unique()
            ->take(5)
            ->values();
    }

    public function render()
    {
        return view('components.toasts.revision-pendiente');
    }
}
