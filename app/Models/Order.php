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
        'upsell',
        'phone',
        'city',
        'adresse',
        'confirmation',
        'affectation',
        'delivery',
        'note',
        'note_d',
        'price',
        'reported_agente_note',
        'reported_delivery_note',
        'reported_agente_date',
        'reported_delivery_date',
        'counts_from_warehouse'
    ];

    protected $casts = [
        'counts_from_warehouse' => 'boolean'
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
}
