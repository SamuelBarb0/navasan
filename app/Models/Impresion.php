<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Impresion extends Model
{
    protected $table = 'impresiones';

    protected $fillable = [
        'orden_id',
        'tipo_impresion',
        'maquina',
        'cantidad_pliegos',
        'cantidad_pliegos_impresos',
        'inicio_impresion',
        'fin_impresion',
        'estado',
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_id');
    }

    public function getEstadoColorAttribute()
    {
        return match ($this->estado) {
            'espera'     => 'warning',   // Naranja
            'proceso'    => 'primary',   // Azul
            'completado' => 'success',   // Verde
            'rechazado'  => 'danger',    // Rojo
            default      => 'secondary',
        };
    }
}
