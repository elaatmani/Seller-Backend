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
        
        'old_product',
        'new_product',
        'old_price',
        'new_price',
        'old_quantity',
        'new_quantity',
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

    public function oldProduct(){
        return $this->belongsTo(Product::class,'old_product');
    }

    public function newProduct(){
        return $this->belongsTo(Product::class,'new_product');
    }
}
