<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\RoadRunnerService;


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
        'comment',
        'withdrawal_method_id',
        'attachement_image'
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

    protected $with = ['delivery','seller', 'withdrawal_method'];
    protected $appends = ['seller_order_count','delivery_order_count','seller_order_price','delivery_order_price'];
    protected $hidden = ['seller_orders','delivery_orders'];

    public function delivery(){
       return $this->belongsTo(User::class,'delivery_id');
    }

    public function history()
    {
        return $this->morphMany(History::class, 'trackable');
    }

    public function seller(){
        return $this->belongsTo(User::class,'user_id');
     }

     public function fees(){
        return $this->hasMany(FactorisationFee::class ,'factorisation_id');
     }

     public function seller_orders(){
        $paidByDeliveryImplemetedAt = '2024-05-17';
        $isDeliveredImplemetedAt = '2024-05-30';
        // return $this->hasMany(Order::class , 'seller_factorisation_id')->where('confirmation', 'confirmer')->where('is_paid_by_delivery', 1);
        return $this->hasMany(Order::class, 'seller_factorisation_id')
            ->where('confirmation', 'confirmer')
            ->when($this->created_at > $paidByDeliveryImplemetedAt, function($query) use ($paidByDeliveryImplemetedAt) {
                return $query->where('is_paid_by_delivery', 1);
            })
            ->when($this->created_at > $isDeliveredImplemetedAt, function($query) use ($paidByDeliveryImplemetedAt) {
                return $query->where('is_delivered', 1);
            });
     }

     public function getSellerOrderCountAttribute()
     {
         return $this->seller_orders->count();
     }

     public function getSellerOrderPriceAttribute()
     {
         $totalRevenue = $this->seller_orders->flatMap(function ($order) {
            return [$order->price ?? 0, ...$order->items->pluck("price")];
        })->sum();

        $totalFees = $this->shippingFees() + $this->totalCOD();

        $otherFees = $this->fees->sum('feeprice');

        $productCostFees = $this->productCostFees();

        $netPayment = $totalRevenue - ($totalFees + $otherFees + $productCostFees);

        return round($netPayment, 2);
     }

     public function delivery_orders(){
        return $this->hasMany(Order::class , 'factorisation_id');
     }

     public function withdrawal_method(){
        return $this->belongsTo(WithdrawalMethod::class , 'withdrawal_method_id', 'id');
     }

     public function getDeliveryOrderCountAttribute()
     {
         return $this->delivery_orders->count();
     }

     public function getDeliveryOrderPriceAttribute()
     {
        $totalPrice = $this->delivery_orders->flatMap(function ($order) {
            return [$order->price ?? 0, ...$order->items->pluck("price")];
        })->sum();

        return $totalPrice;
     }


    /**
     * Calculate the total shipping fees for all orders.
     *
     * @return float
     */
    private function shippingFees()
    {
        $shippingFees = $this->seller_orders->sum(function ($order) {
            return $order->upsell == "oui" ? 10 : 8;
        });

        return $shippingFees;
    }
    
    /**
     * Calculate the total product cost fees for all orders.
     *
     * @return float
     */
    public function productCostFees()
    {
        
        $productCostFees = $this->seller_orders->map(
            function ($order) {
                return $order->items->sum(function ($item) {
                    $product = $item->product;
                    return $product->product_type == 'affiliate' ? $product->selling_price * $item->quantity : 0;
                });
            }
        )->sum();

        return $productCostFees;
    }


    /**
     * Calculate the total COD fees for all orders.
     *
     * @return float
     */
    private function totalCOD()
    {
        $totalCOD = $this->seller_orders->sum(function ($order) {
            return RoadRunnerService::getPrice($order) * 0.04;
        });

        return $totalCOD;
    }

    public function getTotalCodFeesAttribute() {
        return $this->totalCOD();
    }

    public function getTotalShippingFeesAttribute() {
        return $this->shippingFees();
    }
}
