<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenEtapa extends Model
{
    protected $fillable = [
        'orden_produccion_id',
        'etapa_produccion_id',
        'usuario_id',
        'estado',
        'inicio',
        'fin',
        'observaciones',
    ];

    public function orden()
    {
        return $this->belongsTo(\App\Models\OrdenProduccion::class, 'orden_produccion_id');
    }

    public function etapa()
    {
        return $this->belongsTo(\App\Models\EtapaProduccion::class, 'etapa_produccion_id');
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id');
    }
}
