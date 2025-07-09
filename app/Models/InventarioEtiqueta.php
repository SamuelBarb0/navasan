<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioEtiqueta extends Model
{
    protected $fillable = ['orden_id', 'cantidad', 'fecha_programada','observaciones', 'alertado'];

    public function orden()
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_id');
    }
}
