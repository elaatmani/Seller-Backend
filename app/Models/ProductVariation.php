<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductVariation extends Model
{
    use HasFactory;


    protected $fillable = [
        'product_id',
        'product_ref',
        'quantity',
        'warehouse_id',
        'size',
        'color',
        'stockAlert'
    ];

    protected $casts = [
        'product_id' => 'integer',
        'product_ref' => 'string',
        'quantity' => 'integer',
        'warehouse_id' => 'integer',
        'size' => 'string',
        'color' => 'string',
        'stockAlert' => 'integer'
    ];

    public function products(){
        return  $this->belongsTo(Product::class,'product_id');
    }

    public function product(){
        return  $this->belongsTo(Product::class,'product_id');
    }

    public function warehouse(){
        return  $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
    public function getAvailableQuantityAttribute() {
        // return ($this->delivered_quantity);
        return $this->quantity - ($this->shipped_quantity + $this->delivered_quantity);
    }

    public function getShippedQuantityAttribute() {
        return DB::table('order_items')
        ->join('orders', 'orders.id', '=', 'order_items.order_id')
        ->where('product_variation_id', $this->id)
        ->whereIn('confirmation', ['confirmer'])
        ->whereIn('delivery', ['expidier', 'transfer'])
        ->sum('quantity');
    }


    public function getDeliveredQuantityAttribute() {
        return DB::table('order_items')
        ->join('orders', 'orders.id', '=', 'order_items.order_id')
        ->where('product_variation_id', $this->id)
        ->whereIn('confirmation', ['confirmer'])
        ->whereIn('delivery', ['livrer', 'paid'])
        ->sum('quantity');
    }

    public function formatForOrder() {
        return [
            'id' => $this->id,
            'size' => $this->size,
            'color' => $this->color,
            'warehouse_id' => $this->warehouse_id
        ];
    }

    public function formatForShow() {
        return [
            'id' => $this->id,
            'size' => $this->size,
            'color' => $this->color,
            'quantity' => $this->quantity,
            'available_quantity' => $this->available_quantity,
            'shipped_quantity' => $this->shipped_quantity,
            'delivered_quantity' => $this->delivered_quantity,
            'warehouse_id' => $this->warehouse_id
        ];
    }
    // public function formatForOrder() {
    //     return [
    //         'id' => $this->id,
    //         'size' => $this->size,
    //         'color' => $this->color,
    //         'warehouse_id' => $this->warehouse_id
    //     ];
    // }

}
