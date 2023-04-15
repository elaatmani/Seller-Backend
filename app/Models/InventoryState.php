<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryState extends Model
{
    use HasFactory;

    protected $fillable = [
       'product_id',
       'quantity'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventoryStateVariations(){
        return $this->hasMany(InventoryStateVariation::class);
    }
}
