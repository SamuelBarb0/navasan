<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioInsumo extends Model
{
    protected $table = 'inventario_insumos'; // Asegura el nombre correcto de la tabla

    protected $fillable = [
        'insumo_id',
        'cantidad_disponible',
    ];

    public $timestamps = true; // Por si necesitas created_at y updated_at

    /**
     * Relación con el modelo Insumo
     */
    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class);
    }
}
