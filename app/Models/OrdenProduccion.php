<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenProduccion extends Model
{
    protected $fillable = [
        'cliente_id', 'numero_orden', 'fecha', 'estado',
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
}
