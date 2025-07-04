<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsumoRecepcion extends Model
{
    protected $table = 'insumo_recepciones';

    use HasFactory;

    protected $fillable = [
        'insumo_id',
        'cantidad_recibida',
        'tipo_recepcion',
        'fecha_recepcion',
        'archivo_factura',
    ];

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }
}
