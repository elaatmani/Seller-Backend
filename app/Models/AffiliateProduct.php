<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateProduct extends Product
{
    use HasFactory;

    protected $appends = [];
    protected $with = [];

}
