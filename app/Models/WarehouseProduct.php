<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseProduct extends Model
{
    use HasFactory;


    protected $fillable = [
        'product_id',
        'warehouse_id',
        'product_variation_id',
        'quantity'
    ];

    protected $casts = [
        'product_id' => 'integer',
        'warehouse_id'  => 'integer',
        'product_variation_id' => 'integer',
        'quantity' => 'integer'
    ];

    public function products(){
        $this->belongsTo(Product::class,'product_id');
    }

    public function warehouses(){
        $this->belongsTo(Warehouse::class,'warehouse_id');
    }

    public function productVariations(){
        $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }
}
