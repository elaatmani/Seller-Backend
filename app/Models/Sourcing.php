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
        'additional_fees'
    ];
}
