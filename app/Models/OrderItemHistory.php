<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemHistory extends Model
{
    use HasFactory;
    


    protected $fillable = [
        'order_id',
        'item_id',
        'user_id',
        'product',
        'price',
        'quantity',
    ];

    public function orders(){
        return $this->belongsTo(Order::class,'order_id');
    }

    public function users(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function items(){
        return $this->belongsTo(OrderItem::class,'item_id');
    }
}
