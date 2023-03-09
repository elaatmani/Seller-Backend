<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAgente extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'agente_id'
    ];
}
