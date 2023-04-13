<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'historique',
        'note',
        'type'
    ];

    public function orders(){
        return $this->belongsTo(Order::class,'order_id');
    }

    public function users(){
        return $this->belongsTo(User::class,'user_id');
    }
}
