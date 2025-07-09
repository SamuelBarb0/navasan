<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devolucion extends Model
{
    protected $table = 'devoluciones';
    
    protected $fillable = [
        'orden_id',
        'motivo_cliente',
        'revisadora_asignada',
        'tipo_error',
        'codigo_rojo',
        'comentarios_adicionales',
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenProduccion::class);
    }
}

