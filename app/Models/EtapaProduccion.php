<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtapaProduccion extends Model
{
    protected $fillable = [
        'nombre',
        'orden',
        'usuario_id', // ✅ esto es lo que faltaba
    ];

    public function ordenes()
    {
        return $this->hasMany(OrdenEtapa::class);
    }

    public function responsable()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id');
    }

    public function getAreaAttribute()
    {
        return match ($this->nombre) {
            'Preprensa' => 'preprensa',
            'Impresión' => 'impresion',
            'Acabados' => 'acabados',
            'Revisión' => 'revision',
            'Logística' => 'logistica',
            default => null,
        };
    }
}
