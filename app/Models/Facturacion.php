<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facturacion extends Model
{
    protected $table = 'facturaciones';

    protected $fillable = [
        'orden_id',
        'cantidad_final',
        'costo_unitario',
        'total',
        'estado_facturacion',
        'fecha_entrega',
        'metodo_entrega',
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_id');
    }
}
