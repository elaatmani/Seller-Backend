<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fullname',
        'agente_id',
        'parent_id',
        'factorisation_id',
        'seller_factorisation_id',
        'upsell',
        'phone',
        'city',
        'adresse',
        'confirmation',
        'affectation',
        'delivery',
        'note',
        'cancel_reason',
        'price',
        'reported_agente_note',
        'reported_delivery_note',
        'reported_agente_date',
        'reported_delivery_date',
        'counts_from_warehouse',
        'delivery_date',
        'sheets_id',
        'cmd',
        'product_name',
        'source',
        'dropped_at',
        'followup_id',
        'followup_confirmation',
        'followup_reported_note',
        'followup_reported_date',
        'is_delivered',
        'is_canceled',
        'is_affiliate'
    ];

    protected $casts = [
        'user_id' => 'integer', 
        'counts_from_warehouse' => 'boolean',
        'fullname' => 'string',
        'agente_id' => 'integer',
        'confirmation' => 'string',
        'factorisation_id',
        'upsell' => 'string',
        'phone' => 'string',
        'city' => 'string',
        'adresse' => 'string',
        'followup_id' => 'integer',
        'followup_confirmation' => 'string',
        // 'followup_reported_date' => 'date',
        'followup_reported_note' => 'string',
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
        'double' => 'integer',
        'source' => 'string',
        'is_delivered' => 'boolean',
        'is_canceled' => 'boolean',
        'is_affiliate' => 'boolean',
    ];

    protected $with = ['seller_user','items' => ['product_variation.warehouse', 'product'], 'factorisations'];

    protected $appends = [
        'is_done',
        'has_doubles',
        'is_double',
        'delivery_fullname',
        'created_by'
    ];


    public function order_histories()
    {
        return $this->hasMany(OrderHistory::class);
    }

    public function items() {
        return $this->hasMany(OrderItem::class);
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
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

    public function delivery_user() {
        return $this->belongsTo(User::class, 'affectation', 'id');
    }

    public function getDeliveryFullnameAttribute() {
        return !$this->delivery_user ? null : $this->delivery_user->firstname . ' ' . $this->delivery_user->firstname;
    }

    public function getHasDoublesAttribute() {
        return $this->doubles->count() > 0;
    }

    public function getIsDoubleAttribute() {
        return !!$this->double;
    }

    public function advertisements(){
        return $this->hasMany(Ads::class, 'source', 'source');
    }
    public function seller_user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function getCreatedByAttribute() {
        if (strpos($this->sheets_id, 'created_by:') === 0) {
            $userId = (int)substr($this->sheets_id, strlen('created_by:'));
            return User::find($userId);
        } else {
            return null;
        }
    }
    



}
