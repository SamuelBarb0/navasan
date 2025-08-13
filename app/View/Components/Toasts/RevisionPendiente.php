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
        if (!session('mostrar_toast_revision')) {
            $this->ordenes = collect();
            return;
        }

        // Nombres de tabla reales (evita hardcodear 'revisiones' / 'ordenes')
        $rev = (new Revision())->getTable();          // 'revisiones'
        $ord = (new OrdenProduccion())->getTable();   // ej. 'ordenes' o el que tenga tu modelo

        $this->ordenes = Revision::query()
            ->join($ord, "$rev.orden_id", '=', "$ord.id")
            ->whereNotNull("$ord.numero_orden")
            // Si quieres solo las “pendientes” (ej. problemáticas), descomenta:
            // ->whereIn("$rev.tipo", ['apartada', 'defectos', 'rechazada'])
            ->orderBy("$rev.created_at", 'desc')
            ->distinct()
            ->limit(5)
            ->pluck("$ord.numero_orden")
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->values();
    }

    public function render()
    {
        return view('components.toasts.revision-pendiente');
    }
}
