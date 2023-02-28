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

    public function variations(){
        return $this->hasMany('App\Models\ProductVariation');
    }
    
}
