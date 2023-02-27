<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

  
    protected $fillable = [
        'name',
        'selling_price',
        'buying_price',
        'quantity',
        'size',
        'color',
        'image',
        'description'
    ];

    public function variations(){
        return $this->hasMany('App\Models\ProductVariation');
    }
    
}
