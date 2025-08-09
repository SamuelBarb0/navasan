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

    public function getImagenUrlAttribute(): ?string
    {
        $path = $this->imagen_path; // tu columna real
        if (!$path) return null;

        // Si ya es una URL completa, se devuelve igual
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        // Normaliza por si tiene / al inicio
        $path = ltrim($path, '/');

        // Genera URL directa desde public/
        return asset($path); // https://navasan.site/images/productos/archivo.png
    }
}
