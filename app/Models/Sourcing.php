<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sourcing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_name',
        'product_url',
        'estimated_quantity',
        'destination_country',
        'note_by_seller',
        'note_by_admin',
        'shipping_method',
        'quotation_status',
        'sourcing_status',
        'cost_per_unit',
        'total_cost',
        'buying_price',
        'video_url',
        'additional_fees',
        'paid_at',
        'is_paid',
        'buying_price'
    ];

    protected $appends = ['seller_name'];


    public function history()
    {
        return $this->morphMany(History::class, 'trackable');
    }

    public function seller() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getSellerNameAttribute() {
        return $this->seller ? $this->seller->firstname . ' ' . $this->seller->lastname : "Not Found ($this->user_id)";
    }
}
