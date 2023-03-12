<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryPlace extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'city_id',
        'fee'
    ];
}
