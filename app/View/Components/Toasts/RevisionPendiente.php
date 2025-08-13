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
        // Consumimos la bandera una vez
        $flag = session()->pull('mostrar_toast_revision', false);
        $this->shouldShow = (bool) $flag;
        $this->ordenes = collect();

        if (!$this->shouldShow) {
            return;
        }

        $rev = (new Revision())->getTable();          // 'revisiones'
        $ord = (new OrdenProduccion())->getTable();   // 'ordenes' (o el que tengas)

        // Armamos una consulta "raw" para inspección
        $query = Revision::query()
            ->join($ord, "$rev.orden_id", '=', "$ord.id")
            ->whereNotNull("$ord.numero_orden")
            ->orderBy("$rev.created_at", 'desc');

        // ⚠️ DEBUG: si pasas ?dd_toast=1, hacemos dd() y paramos.
        if (request()->boolean('dd_toast')) {
            $rawRows = $query
                ->select([
                    "$rev.id as revision_id",
                    "$rev.tipo",
                    "$rev.cantidad",
                    "$rev.created_at as revision_fecha",
                    "$ord.id as orden_id",
                    "$ord.numero_orden",
                ])
                ->limit(20)
                ->get();

            dd([
                'flag_session_consumida' => $flag,
                'total_rows'             => $rawRows->count(),
                'rows'                   => $rawRows->toArray(),
                'solo_numeros_orden'     => $rawRows->pluck('numero_orden')->all(),
            ]);
        }

        // Flujo normal (sin dd):
        $this->ordenes = $query
            ->distinct()
            ->limit(5)
            ->pluck("$ord.numero_orden")
            ->map(fn ($v) => trim((string) $v))
            ->filter()
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
