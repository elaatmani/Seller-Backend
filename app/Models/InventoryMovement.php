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
        'is_received',
        'note'
    ];

    protected $casts = [
        'delivery_id' => 'integer',
        'product_id' => 'integer',
        'is_received' => 'integer',
    ];

    public function delivery()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventory_movement_variations(){
        return $this->hasMany(InventoryMovementVariation::class, 'inventory_movement_id');
    }

}
