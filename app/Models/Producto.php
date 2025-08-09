<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Producto extends Model
{
    protected $fillable = [
        'cliente_id',
        'codigo',
        'nombre',
        'presentacion',
        'unidad',
        'activo',
        'imagen',   // path relativo en storage o public
        'precio',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'precio' => 'decimal:2',
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // (opcional) si quieres acceder desde el producto a inventario de etiquetas
    public function inventarioEtiquetas()
    {
        return $this->hasMany(InventarioEtiqueta::class, 'producto_id');
    }

    public function getImagenUrlAttribute(): ?string
    {
        // Si tienes una columna imagen_url “manual”, respétala
        if (!empty($this->attributes['imagen_url']) && preg_match('#^https?://#i', $this->attributes['imagen_url'])) {
            return $this->attributes['imagen_url'];
        }

        $path = $this->imagen_path ?? $this->attributes['imagen_url'] ?? null;
        if (!$path) return null;

        if (preg_match('#^https?://#i', $path)) return $path;
        return asset(ltrim($path, '/'));
    }


    // Scope útil para listar solo activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
