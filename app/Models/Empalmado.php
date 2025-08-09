<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empalmado extends Model
{
    protected $table = 'empalmados';

    protected $fillable = [
        'orden_id',
        'proceso', // 'empalmado'
        'realizado_por',
        'cantidad_pliegos_impresos',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_fin' => 'datetime',
        'cantidad_pliegos_impresos' => 'integer',
    ];

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
        return $this->belongsTo(\App\Models\Producto::class, 'producto_id');
    }
}
