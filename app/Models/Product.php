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
        'selling_price' => 'integer',
        'buying_price' => 'integer',
        'description' => 'string'
    ];

    public function variations(){
        return $this->hasMany('App\Models\ProductVariation');
    }
    
    public function users()
    {
        return $this->hasManyThrough(User::class, ProductAgente::class, 'product_id', 'id', 'id', 'agente_id');
    }
}
