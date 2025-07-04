<?php
namespace App\Models;

use App\Models\ItemOrden;
use Illuminate\Database\Eloquent\Model;

class ItemEntrega extends Model
{
    protected $fillable = ['item_orden_id', 'fecha_entrega', 'cantidad'];

    public function item()
    {
        return $this->belongsTo(ItemOrden::class, 'item_orden_id');
    }
}
