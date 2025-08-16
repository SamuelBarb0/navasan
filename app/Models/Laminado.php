<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laminado extends Model
{
    protected $table = 'laminados';

    protected $fillable = [
        'orden_id',
        'proceso', // 'laminado_mate' | 'laminado_brillante'
        'realizado_por',
        'producto_id',
        'cantidad_liberada',         // 👈 agregado
        'cantidad_pliegos_impresos', // (opcional) Cantidad Final
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_fin'                 => 'datetime',
        'cantidad_liberada'         => 'integer',
        'cantidad_pliegos_impresos' => 'integer',
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_id');
    }

    public function getProcesoNombreAttribute(): string
    {
        return match ($this->proceso) {
            'laminado_mate'      => 'Laminado Mate',
            'laminado_brillante' => 'Laminado Brillante',
            default              => ucfirst(str_replace('_', ' ', (string) $this->proceso)),
        };
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
