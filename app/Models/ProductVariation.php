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
        'image'
    ];
}
