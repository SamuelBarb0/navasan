<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ItemEntrega;

class ItemOrden extends Model
{
    protected $fillable = [
        'orden_produccion_id',
        'nombre',
        'cantidad',
    ];

    public function ordenProduccion()
    {
        return $this->belongsTo(OrdenProduccion::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function entregas()
    {
        return $this->hasMany(ItemEntrega::class);
    }
}
