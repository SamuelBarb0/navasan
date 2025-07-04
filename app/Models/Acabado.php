<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Acabado extends Model
{
    protected $fillable = ['orden_id', 'proceso', 'realizado_por'];

    public function orden()
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_id');
    }

    public function getProcesoNombreAttribute()
    {
        return match ($this->proceso) {
            'laminado_mate' => 'Laminado Mate',
            'laminado_brillante' => 'Laminado Brillante',
            'empalmado' => 'Empalmado',
            'suaje' => 'Suaje',
            'corte_guillotina' => 'Corte Guillotina',
        };
    }
}
