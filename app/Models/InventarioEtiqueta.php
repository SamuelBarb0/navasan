<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioEtiqueta extends Model
{
    protected $fillable = ['orden_id', 'item_orden_id', 'cantidad', 'fecha_programada', 'estado', 'observaciones', 'alertado'];


    public function itemOrden()
    {
        return $this->belongsTo(ItemOrden::class, 'item_orden_id');
    }

    public function orden()
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_id');
    }
}
