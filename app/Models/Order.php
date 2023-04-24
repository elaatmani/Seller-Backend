<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'fullname',
        'product_name',
        'agente_id',
        'upsell',
        'phone',
        'city',
        'adresse',
        'quantity',
        'confirmation',
        'affectation',
        'delivery',
        'note',
        'note_d',
        'price',
        'reported_agente_note',
        'reported_delivery_note',
        'reported_agente_date',
        'reported_delivery_date'
    ];


    public function order_histories()
    {
        return $this->hasMany(OrderHistory::class);
    }
}
