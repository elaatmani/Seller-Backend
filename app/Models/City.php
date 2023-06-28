<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'name', 'roadrunner_city_id', 'roadrunner_zone_id'];


    public function deliveryPlaces()
    {
        return $this->belongsToMany(DeliveryPlace::class);
    }
}
