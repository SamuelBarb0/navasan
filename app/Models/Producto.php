<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = [
        'cliente_id',
        'codigo',
        'nombre',
        'presentacion',
        'unidad',
        'activo',
        'imagen',     // <- Nuevo campo
        'precio',     // <- Nuevo campo
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
