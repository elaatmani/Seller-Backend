<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factorisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'factorisation_id',
        'type',
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
        'user_id' => 'integer',
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

    public function seller(){
        return $this->belongsTo(User::class,'user_id');
     }

     public function fees(){
        return $this->hasMany(FactorisationFee::class ,'factorisation_id');
     }
}
