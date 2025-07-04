<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'nombre', 'nit', 'telefono',
    ];

    public function ordenesProduccion()
    {
        return $this->hasMany(OrdenProduccion::class);
    }
}
