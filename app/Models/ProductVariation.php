<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    use HasFactory;


    protected $fillable = [
        'product_id',
        'product_ref',
        'quantity',
        'warehouse_id',
        'size',
        'color',
        'stockAlert'
    ];

    protected $casts = [
        'product_id' => 'integer',
        'product_ref' => 'string',
        'quantity' => 'integer',
        'warehouse_id' => 'integer',
        'size' => 'string',
        'color' => 'string',
        'stockAlert' => 'integer'
    ];

    public function products(){
        return  $this->belongsTo(Product::class,'product_id');
    }

    public function product(){
        return  $this->belongsTo(Product::class,'product_id');
    }

    public function warehouse(){
        return  $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

}
