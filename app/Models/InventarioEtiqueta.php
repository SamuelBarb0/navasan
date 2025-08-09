<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioEtiqueta extends Model
{
    protected $fillable = ['orden_id', 'item_orden_id', 'producto_id', 'cantidad', 'fecha_programada', 'estado', 'observaciones', 'alertado'];


    public function itemOrden()
    {
        return $this->belongsTo(ItemOrden::class, 'item_orden_id');
    }

    public function orden()
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function cliente()
    {
        return $this->belongsTo(\App\Models\Cliente::class, 'cliente_id');
    }

    // Si no lo tienes aún, agrégalo al modelo
    public function getImagenUrlAttribute()
    {
        // imagen_path = 'images/productos/archivo.png'
        return $this->imagen_path ? url($this->imagen_path) : null;
    }
}
