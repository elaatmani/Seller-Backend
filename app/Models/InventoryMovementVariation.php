<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovementVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_movement_id',
        'size',
        'color',
        'quantity'
    ];

    public function inventoryMovements(){
        return $this->belongsTo(InventoryMovement::class,'inventory_movement_id');
    }
}
