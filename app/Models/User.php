<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable , HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'phone',
        'photo',
        'email',
        'password',
        'status',
        'last_action',
        'having_all'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $with = [ 'productsDelivery' ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['fullname'];


    public function role()
    {
        return $this->belongsTo('Spatie\Permission\Models\Role');
    }

    public function products()
    {
        return $this->hasManyThrough(Product::class, ProductAgente::class, 'agente_id', 'id', 'id', 'product_id');
    }



    public function deliveryPlaces()
    {
        return $this->hasMany(DeliveryPlace::class, 'delivery_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city');
    }

    public function order_histories()
    {
        return $this->hasMany(OrderHistory::class);
    }

    public function productsDelivery(){
        return $this->hasMany(ProductDelivery::class,'delivery_id');
    }

    public function getActiveAttribute() {
        return !!$this->last_action ? Carbon::make($this->last_action)->diffForHumans() : null;
    }

    public function getFullnameAttribute() {
        return $this->firstname . ' ' . $this->lastname;
    }

}
