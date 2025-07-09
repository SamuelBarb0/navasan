<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReporteRevisadoController extends Controller
{
    public function index()
    {
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        // Obtener todos los registros de revisiones con orden relacionada
        $revisiones = DB::table('revisiones')
            ->join('orden_produccions', 'revisiones.orden_id', '=', 'orden_produccions.id')
            ->select(
                'revisiones.revisado_por',
                'orden_produccions.numero_orden',
                'orden_produccions.urgente',
                'revisiones.orden_id',
                'revisiones.cantidad',
                'revisiones.created_at'
            )
            ->whereBetween('revisiones.created_at', [$start, $end])
            ->get();

        // Agrupar por revisador
        $agrupado = $revisiones->groupBy('revisado_por')->map(function ($items, $nombre) {
            return (object)[
                'revisado_por' => $nombre,
                'ordenes_normales' => $items->where('urgente', false)->pluck('orden_id')->unique()->count(),
                'ordenes_urgentes' => $items->where('urgente', true)->pluck('orden_id')->unique()->count(),
                'total_revisado' => $items->sum('cantidad'),
                'detalles' => $items->map(function ($item) {
                    return (object)[
                        'numero_orden' => $item->numero_orden,
                        'fecha' => $item->created_at,
                        'urgente' => $item->urgente,
                        'cantidad_revisada' => $item->cantidad,
                    ];
                })
            ];
        })->values(); // Para que sea una colección numérica y no asociativa

        return view('reportes.revisado', [
            'reporte' => $agrupado,
            'start' => $start,
            'end' => $end,
        ]);
    }
}
