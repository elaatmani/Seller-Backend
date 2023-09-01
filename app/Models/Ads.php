<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ads extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'source',
        'amount',
        'ads_at'
    ];

    protected $casts = [
        'product_id' => 'integer',
        'source' => 'string',
        'amount' => 'float',
        'ads_at' => 'date'
    ];

    protected $with = ['products'];

    public function orders(){
        return $this->belongsTo(Order::class,'source', 'source');
    }

    public function products(){
        return $this->belongsTo(Product::class,'product_id');
    }
  
}
