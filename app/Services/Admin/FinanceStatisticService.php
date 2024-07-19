<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Http;

class FinanceStatisticService
{

    public static function getRevenue($from = null, $to = null, $seller_id = null) {
        $result = DB::table('order_items')
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->where('orders.confirmation', '=', 'confirmer')
        ->select(
            DB::raw('SUM(CASE WHEN orders.delivery = "livrer" THEN order_items.price ELSE 0 END) as sum_livrer'),
            DB::raw('SUM(CASE WHEN orders.delivery = "livrer" AND NOT is_paid_by_delivery THEN order_items.price ELSE 0 END) as not_paid_by_delivery'),
            DB::raw('SUM(CASE WHEN orders.delivery = "paid" THEN order_items.price ELSE 0 END) as sum_paid'),
            DB::raw('count(DISTINCT CASE WHEN orders.delivery = "paid" THEN order_items.order_id ELSE NULL END) as count_orders'),
            DB::raw('count(DISTINCT CASE WHEN orders.delivery in ("paid", "livrer") THEN order_items.order_id ELSE NULL END) as count_orders_paid_delivered')
            // DB::raw('SUM(CASE WHEN orders.delivery != "paid" AND orders.is_paid_by_delivery AND NOT orders.is_paid_to_seller THEN order_items.price ELSE 0 END) as sum_to_be_paid'),
            // DB::raw('SUM(CASE WHEN orders.delivery = "cleared" THEN order_items.price ELSE 0 END) as sum_cleared'),
            // DB::raw('SUM(order_items.price) as sum_total')
        )
        ->first();
        
        $fees = DB::table('factorisation_fees')
        ->join('factorisations', 'factorisation_fees.factorisation_id', '=', 'factorisations.id')
        ->where([
            'close' => 1,
            'paid' => 1
        ])->sum('factorisation_fees.feeprice');

        $result->cod_fees =  $result->sum_paid * 0.04;
        $result->shipping_fees =  $result->count_orders * 8;
        $result->other_fees =  $fees;
        $result->net_paid =  $result->sum_paid - ($result->cod_fees + $result->shipping_fees + $fees);

        return $result;
    }


    public static function getAverageOrderValue($from = null, $to = null, $seller_id = null) {
        $ordersCount = DB::table('orders')
            ->where('orders.confirmation', '=', 'confirmer')
            ->whereIn('orders.delivery', ['paid', 'cleared', 'livrer'])
            ->count();
                
        $revenue = self::getRevenue();
        $totalRevenue = $revenue->sum_livrer + $revenue->sum_paid;
            
        return $totalRevenue > 0 ? round($totalRevenue / $ordersCount, 2) : 0;
    }

    public static function getDeliveredOrdersRevenue($from = null, $to = null, $seller_id = null) {
                
        $result = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.confirmation', '=', 'confirmer')
            ->whereIn('orders.delivery', ['livrer'])
            ->sum('order_items.price'); 
            
        return $result;
    }

    public static function getPaidOrdersRevenue($from = null, $to = null, $seller_id = null) {
                
        $result = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.confirmation', '=', 'confirmer')
            ->whereIn('orders.delivery', ['paid', 'cleared'])
            ->sum('order_items.price'); 
            
        return $result;
    }

    

}