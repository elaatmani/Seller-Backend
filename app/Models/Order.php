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
        'sheets_id',
        'cmd',
        'product_name'
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
        'price' => 'float',
        'reported_agente_note' => 'string',
        'reported_delivery_note' => 'string',
        // 'reported_agente_date' => 'date',
        // 'reported_delivery_date' => 'date',
        'delivery_date'  => 'datetime',
        'cmd'  => 'string',
        'double' => 'integer'
    ];

    protected $with = ['items' => ['product_variation.warehouse', 'product'], 'factorisations'];

    protected $appends = [
        'is_done',
        'has_doubles',
        'is_double'
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

    public function doubles() {
        return $this->hasMany(Order::class, 'double', 'id');
    }

    // public function origin() {
    //     return $this->belongsTo(Order::class, 'id', 'double');
    // }

    public function getHasDoublesAttribute() {
        return $this->doubles->count() > 0;
    }

    public function getIsDoubleAttribute() {
        return !!$this->double;
    }


}
