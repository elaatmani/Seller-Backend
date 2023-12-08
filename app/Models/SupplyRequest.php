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
        'status',
        'note'
    ];

    public function history()
    {
        return $this->morphMany(History::class, 'trackable', 'reference_table', 'reference_id');
    }
}
