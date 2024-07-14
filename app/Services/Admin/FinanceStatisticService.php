<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FinanceStatisticService
{

    public static function getAverageOrderValue($from = null, $to = null, $seller_id = null) {
        $ordersCount = DB::table('orders')
            ->where('orders.confirmation', '=', 'confirmer')
            ->whereIn('orders.delivery', ['paid', 'cleared', 'livrer'])
            ->count();
                
        $result = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.confirmation', '=', 'confirmer')
            ->whereIn('orders.delivery', ['paid', 'cleared', 'livrer'])
            ->sum('order_items.price');
            
        return round($result / $ordersCount, 2);
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