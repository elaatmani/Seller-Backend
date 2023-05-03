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
        'size',
        'color',
        'stockAlert'
    ];

    protected $casts = [
        'product_id' => 'integer',
        'product_ref' => 'string',
        'quantity' => 'integer',
        'size' => 'string',
        'color' => 'string',
        'stockAlert' => 'integer'
    ];

    public function products(){
        $this->belongsTo(Product::class,'product_id');
    }
    
}
