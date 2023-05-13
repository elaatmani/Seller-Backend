<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_ref',
        'product_variation_id',
        'quantity'
    ];

    protected $casts = [
        'order_id' => 'integer',
        'product_id' => 'integer',
        'product_ref' => 'string',
        'product_variation_id' => 'integer',
        'quantity' => 'integer'
    ];
}
