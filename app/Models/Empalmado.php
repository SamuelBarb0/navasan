<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Empalmado extends Model
{
    protected $table = 'empalmados';

    protected $fillable = [
        'orden_id',
        'producto_id',
        'proceso', // 'empalmado'
        'realizado_por',
        'cantidad_liberada',          // 👈 nuevo nombre correcto
        'cantidad_pliegos_impresos',  // (opcional) Cantidad Final
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_fin'                 => 'datetime',
        'cantidad_liberada'         => 'integer',
        'cantidad_pliegos_impresos' => 'integer',
    ];

    // Fallback temporal: si aún existe la columna antigua 'cantidad_libe'
    protected function cantidadLiberada(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                if (!is_null($value)) return (int) $value;
                return array_key_exists('cantidad_libe', $attributes)
                    ? (int) $attributes['cantidad_libe']
                    : null;
            },
        );
    }

    public function orden()
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_id');
    }

    public function getProcesoNombreAttribute(): string
    {
        return 'Empalmado';
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
