<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovementVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_movement_id',
        'product_variation_id',
        'quantity',
    ];

    protected $casts = [
        'inventory_movement_id' => 'integer',
        'product_variation_id' => 'integer',
        'quantity' => 'integer',
    ];

    public function inventory_movement() {
        return $this->belongsTo(InventoryMovement::class);
    }

    public function product_variation() {
        return $this->belongsTo(ProductVariation::class);
    }
}
