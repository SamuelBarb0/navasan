<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenProduccion extends Model
{
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
}
