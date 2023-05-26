<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'fullname',
        'agente_id',
        'factorisation_id',
        'upsell',
        'phone',
        'city',
        'adresse',
        'confirmation',
        'affectation',
        'delivery',
        'note',
        'price',
        'reported_agente_note',
        'reported_delivery_note',
        'reported_agente_date',
        'reported_delivery_date',
        'counts_from_warehouse',
        'delivery_date',
        'cmd'
    ];

    protected $casts = [
        'counts_from_warehouse' => 'boolean',
        'fullname' => 'string',
        'agente_id' => 'integer',
        'factorisation_id',
        'upsell' => 'string',
        'phone' => 'string',
        'city' => 'string',
        'adresse' => 'string',
        'confirmation' => 'string',
        'affectation' => 'integer',
        'delivery' => 'string',
        'note' => 'string',
        'price' => 'integer',
        'reported_agente_note' => 'string',
        'reported_delivery_note' => 'string',
        'reported_agente_date' => 'date',
        'reported_delivery_date' => 'date',
        'delivery_date'  => 'datetime',
        'cmd'  => 'string'
    ];

    protected $appends = [
        'is_done'
    ];


    public function order_histories()
    {
        return $this->hasMany(OrderHistory::class);
    }

    public function items() {
        return $this->hasMany(OrderItem::class);
    }

    public function getIsDoneAttribute() {
        return $this->confirmation == 'confirmer' && $this->delivery == 'livrer';
    }

    public function factorisations(){
        return $this->belongsTo(Factorisation::class,'factorisation_id');
    }

    
}
