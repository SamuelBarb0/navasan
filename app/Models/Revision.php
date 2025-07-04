<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
    protected $table = 'revisiones';

    protected $fillable = [
        'orden_id',
        'revisado_por',
        'cantidad',
        'tipo',
        'comentarios',
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_id');
    }
}