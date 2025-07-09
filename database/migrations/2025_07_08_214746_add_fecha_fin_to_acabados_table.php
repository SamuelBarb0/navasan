<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Acabado extends Model
{
    protected $fillable = ['orden_id', 'proceso', 'realizado_por', 'fecha_fin'];

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

    public function getFechaFinFormatoAttribute()
    {
        return $this->fecha_fin ? Carbon::parse($this->fecha_fin)->format('d/m/Y H:i') : null;
    }
}
