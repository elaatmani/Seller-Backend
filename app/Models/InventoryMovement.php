<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'delivery_id',
        'product_variation_id',
        'quantity',
        'is_received',
        'note'
    ];

    public function delivery()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventoryMovementVariations(){
        return $this->hasMany(InventoryMovementVariation::class);
    }

}
