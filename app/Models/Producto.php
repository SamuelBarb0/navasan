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
        if (!$this->imagen_path) return null;

        // Si ya es URL completa
        if (preg_match('#^https?://#i', $this->imagen_path)) {
            return $this->imagen_path;
        }

        // Si el archivo está en /public/images/... (caso de saveUploadedImage)
        return asset($this->imagen_path);

        // Si en algún caso usas storage/app/public, sería:
        // return Storage::url($this->imagen_path);
    }
    
    // Scope útil para listar solo activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
