<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suajes extends Model
{
    protected $table = 'suajes';

    protected $fillable = [
        'orden_id',
        'cantidad_liberada',
        'cantidad_pliegos_impresos',
    ];

    protected $casts = [
        'cantidad_liberada' => 'integer',
        'cantidad_pliegos_impresos' => 'integer',
    ];

    public function orden()
    {
        return $this->belongsTo(\App\Models\OrdenProduccion::class, 'orden_id');
    }
}
