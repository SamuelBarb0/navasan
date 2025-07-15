<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Relación: una categoría tiene muchos insumos.
     */
    public function insumos()
    {
        return $this->hasMany(Insumo::class);
    }
}
