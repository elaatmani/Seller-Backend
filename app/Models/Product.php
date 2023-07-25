<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'ref',
        'selling_price',
        'buying_price',
        'description'
    ];


    protected $casts = [
        'name' => 'string',
        'ref' => 'string',
        'selling_price' => 'float',
        'buying_price' => 'float',
        'description' => 'string'
    ];

    protected $with = [ 'deliveries' ];

    public function variations(){
        return $this->hasMany('App\Models\ProductVariation');
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, ProductAgente::class, 'product_id', 'id', 'id', 'agente_id');
    }

    public function deliveries(){
        return $this->hasMany(ProductDelivery::class,'product_id');
    }

    public function image(){
        return $this->hasOne(ProductImage::class, 'prodcut_id');
    }
}
