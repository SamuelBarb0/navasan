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
        // NO consumimos la bandera mientras depuramos
        $flag = (bool) session()->get('mostrar_toast_revision', false);

        $rev = (new Revision())->getTable();          // 'revisiones'
        $ord = (new OrdenProduccion())->getTable();   // tabla real del modelo OrdenProduccion

        // Trae filas crudas para inspección
        $rawRows = Revision::query()
            ->join($ord, "$rev.orden_id", '=', "$ord.id")
            ->whereNotNull("$ord.numero_orden")
            ->orderBy("$rev.created_at", 'desc')
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

        // 🔴 DD directo: detiene la ejecución aquí
        dd([
            'flag_en_sesion'      => $flag,
            'total_rows'          => $rawRows->count(),
            'rows'                => $rawRows->toArray(),
            'solo_numeros_orden'  => $rawRows->pluck('numero_orden')->all(),
        ]);

        // --- Lo de abajo no se ejecutará mientras exista el dd() ---

        $this->shouldShow = $flag;
        $this->ordenes = collect();

        if (!$this->shouldShow) {
            return;
        }

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

        if ($this->ordenes->isEmpty()) {
            $this->shouldShow = false;
        }
    }

    public function render()
    {
        return view('components.toasts.revision-pendiente');
    }
}
