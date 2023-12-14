<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplyRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_variation_id',
        'quantity',
        'seller_id',
        'status',
        'note',
        'admin_note'
    ];

    public function history()
    {
        return $this->morphMany(History::class, 'trackable');
    }

    public function seller() {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product_variation() {
        return $this->belongsTo(ProductVariation::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
