<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
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

    public function suajes()
    {
        return $this->hasMany(\App\Models\Suajes::class, 'orden_id');
    }

    public static function ordenesListasParaEtapa(string $nombreEtapa)
    {
        $nombreNormalizado = Str::ascii($nombreEtapa);

        $etapaTarget = \App\Models\EtapaProduccion::whereIn('nombre', [
            $nombreEtapa,
            $nombreNormalizado,
        ])->first();

        if (!$etapaTarget) {
            return collect();
        }

        $etapaOrden = $etapaTarget->orden;

        return self::whereHas('etapas', function ($q) use ($etapaTarget) {
            $q->where('etapa_produccion_id', $etapaTarget->id)
                ->whereIn(DB::raw('LOWER(estado)'), ['pendiente', 'en_proceso']);
        })
            ->whereNotExists(function ($subquery) use ($etapaOrden) {
                $subquery->select(DB::raw(1))
                    ->from('orden_etapas as anteriores')
                    ->join('etapa_produccions as ep2', 'anteriores.etapa_produccion_id', '=', 'ep2.id')
                    // 🔴 Correlación con la tabla PADRE, no con el alias del whereHas
                    ->whereColumn('anteriores.orden_produccion_id', 'orden_produccions.id')
                    ->where('ep2.orden', '<', $etapaOrden)
                    ->whereIn(DB::raw('LOWER(anteriores.estado)'), ['pendiente', 'en_proceso']);
            })
            ->latest()
            ->take(20)
            ->get();
    }

    public function scopeListasParaEtapa(Builder $q, string $nombreEtapa): Builder
    {
        $nombreNormalizado = Str::ascii($nombreEtapa);

        $etapaTarget = \App\Models\EtapaProduccion::whereIn('nombre', [
            $nombreEtapa,
            $nombreNormalizado,
        ])->first();

        if (!$etapaTarget) {
            return $q->whereRaw('1=0'); // sin resultados si no existe la etapa
        }

        $etapaOrden = $etapaTarget->orden;

        return $q->whereHas('etapas', function ($q2) use ($etapaTarget) {
            $q2->where('etapa_produccion_id', $etapaTarget->id)
                ->whereIn(DB::raw('LOWER(estado)'), ['pendiente', 'en_proceso']);
        })
            ->whereNotExists(function ($sub) use ($etapaOrden) {
                $sub->select(DB::raw(1))
                    ->from('orden_etapas as anteriores')
                    ->join('etapa_produccions as ep2', 'anteriores.etapa_produccion_id', '=', 'ep2.id')
                    ->whereColumn('anteriores.orden_produccion_id', 'orden_produccions.id')
                    ->where('ep2.orden', '<', $etapaOrden)
                    ->whereIn(DB::raw('LOWER(anteriores.estado)'), ['pendiente', 'en_proceso']);
            });
    }
}
