<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'delivery_id'
    ];

    protected $casts = [
        'product_id' => 'integer',
        'delivery_id' => 'integer',
    ];
}
