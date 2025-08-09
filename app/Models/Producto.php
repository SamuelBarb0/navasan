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

    // Accessor: URL pública de la imagen
    public function getImagenUrlAttribute(): ?string
    {
        if (!$this->imagen) return null;

        // Si guardas en storage/app/public => usar Storage::url
        // Si a veces guardas en /public/images, intenta detectar
        if (preg_match('#^https?://#i', $this->imagen)) {
            return $this->imagen; // ya es URL completa
        }

        // Si el path parece de storage público
        return Storage::url($this->imagen);
        // Si usas public_path('images/...'), podrías usar:
        // return asset($this->imagen);
    }

    // Scope útil para listar solo activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
