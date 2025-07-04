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
        return $this->belongsTo(\App\Models\OrdenProduccion::class);
    }

    public function etapa()
    {
        return $this->belongsTo(\App\Models\EtapaProduccion::class);
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    
}
