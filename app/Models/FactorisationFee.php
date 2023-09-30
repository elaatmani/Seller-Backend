<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactorisationFee extends Model
{
    use HasFactory;
    protected $fillable = [
        'factorisation_id',
        'feename',
        'feeprice'
    ];

    protected $casts =  [
        'factorisation_id' => 'integer',
        'feename' => 'string',
        'feeprice' => 'float'
    ];

}
