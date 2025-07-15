<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    protected $fillable = [
        'nombre',
        'unidad',
        'descripcion',
        'estado',
        'categoria_id',
    ];

    public function ordenes()
    {
        return $this->hasMany(InsumoOrden::class);
    }

    // App\Models\Insumo.php
    public function inventario()
    {
        return $this->hasOne(\App\Models\InventarioInsumo::class, 'insumo_id');
    }

    public function recepciones()
    {
        return $this->hasMany(InsumoRecepcion::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    
}
