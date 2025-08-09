<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrdenProduccion extends Model
{
    protected $table = 'orden_produccions'; // o el nombre correcto real

    protected $fillable = [
        'cliente_id',
        'numero_orden',
        'fecha',
        'area_actual',
        'estado',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function items()
    {
        return $this->hasMany(ItemOrden::class);
    }

    public function etapas()
    {
        return $this->hasMany(OrdenEtapa::class);
    }

    public function insumos()
    {
        return $this->hasMany(InsumoOrden::class);
    }

    public function impresiones()
    {
        return $this->hasMany(\App\Models\Impresion::class, 'orden_id');
    }

    public function acabados()
    {
        return $this->hasMany(\App\Models\Acabado::class, 'orden_id');
    }

    public function revisiones()
    {
        return $this->hasMany(\App\Models\Revision::class, 'orden_id');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'orden_id');
    }

    public function devolucion()
    {
        return $this->hasOne(\App\Models\Devolucion::class, 'orden_id', 'id');
    }



    public static function ordenesListasParaEtapa($nombreEtapa)
    {
        $etapaTarget = \App\Models\EtapaProduccion::where('nombre', $nombreEtapa)->first();

        if (!$etapaTarget) return collect(); // por si no existe

        $etapaOrden = $etapaTarget->orden;

        return self::whereHas('etapas', function ($q) use ($etapaTarget, $etapaOrden) {
            $q->where('etapa_produccion_id', $etapaTarget->id)
                ->where('estado', 'pendiente')
                ->whereNotExists(function ($subquery) use ($etapaOrden) {
                    $subquery->select(DB::raw(1))
                        ->from('orden_etapas as anteriores')
                        ->join('etapa_produccions as ep2', 'anteriores.etapa_produccion_id', '=', 'ep2.id')
                        ->whereColumn('anteriores.orden_produccion_id', 'orden_etapas.orden_produccion_id')
                        ->where('ep2.orden', '<', $etapaOrden)
                        ->whereIn('anteriores.estado', ['pendiente', 'en_proceso']);
                });
        })
            ->latest()
            ->take(10)
            ->get();
    }
}
