<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factorisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'factorisation_id',
        'type',
        'delivery_id',
        'close',
        'paid',
        'commands_number',
        'price',
        'close_at',
        'paid_at',
        'comment'
    ];


    protected $casts = [
        'user_id' => 'integer',
        'factorisation_id' => 'string',
        'delivery_id' => 'integer',
        'close' => 'boolean',
        'paid' => 'boolean',
        'commands_number' => 'integer',
        'price' => 'float',
        'close_at' => 'datetime',
        'paid_at' => 'datetime',
        'comment' => 'string'
    ];

    protected $with = ['delivery','seller'];
    protected $appends = ['seller_order_count','delivery_order_count','seller_order_price','delivery_order_price'];
    protected $hidden = ['seller_orders','delivery_orders'];

    public function delivery(){
       return $this->belongsTo(User::class,'delivery_id');
    }

    public function seller(){
        return $this->belongsTo(User::class,'user_id');
     }

     public function fees(){
        return $this->hasMany(FactorisationFee::class ,'factorisation_id');
     }

     public function seller_orders(){
        return $this->hasMany(Order::class , 'seller_factorisation_id');
     }

     public function getSellerOrderCountAttribute()
     {
         return $this->seller_orders->count();
     }

     public function getSellerOrderPriceAttribute()
     {
        $totalPrice = $this->seller_orders->flatMap(function ($order) {
            return [$order->price ?? 0, ...$order->items->pluck("price")];
        })->sum();

        return $totalPrice;
     }

     public function delivery_orders(){
        return $this->hasMany(Order::class , 'factorisation_id');
     }

     public function getDeliveryOrderCountAttribute()
     {
         return $this->delivery_orders->count();
     }

     public function getDeliveryOrderPriceAttribute()
     {
        $totalPrice = $this->delivery_orders->flatMap(function ($order) {
            return [$order->price ?? 0, ...$order->items->pluck("price")];
        })->sum();

        return $totalPrice;
     }
}
