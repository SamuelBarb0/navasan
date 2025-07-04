<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsumoOrden extends Model
{
    protected $table = 'insumo_orden';

    protected $fillable = [
        'orden_produccion_id',
        'insumo_id',
        'cantidad_requerida',
        'cantidad_recibida',
        'estado',
        'tipo_recepcion',
        'fecha_recepcion',
        'factura_archivo',
    ];

    protected $dates = ['fecha_recepcion'];

    public function orden()
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_produccion_id');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }
}
