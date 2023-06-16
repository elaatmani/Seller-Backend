<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factorisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'factorisation_id',
        'delivery_id',
        'close',
        'paid',
        'commands_number',
        'price',
        'close_at',
        'paid_at',
        'comment'
    ];


    protected $casts = [
        'factorisation_id' => 'string',
        'delivery_id' => 'integer',
        'close' => 'boolean',
        'paid' => 'boolean',
        'commands_number' => 'integer',
        'price' => 'float',
        'close_at' => 'datetime',
        'paid_at' => 'datetime',
        'comment' => 'string'
    ];

    protected $with = ['delivery'];


    public function delivery(){
       return $this->belongsTo(User::class,'delivery_id');
    }
}
