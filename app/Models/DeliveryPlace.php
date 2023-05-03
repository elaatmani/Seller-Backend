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

    protected $casts = [
        'delivery_id' => 'integer',
        'city_id' => 'integer',
        'fee' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'delivery_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

   
}
