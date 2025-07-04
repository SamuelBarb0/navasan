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

}
