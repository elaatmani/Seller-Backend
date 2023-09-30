<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ads;
use App\Models\OrderHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AnalyticsService
{

    public static $confirmations = [
        null => 'New',
        'day-one-call-one'=> 'No reply 1 / day1',
        'day-one-call-two' =>'No reply 2 / day1',
        'day-one-call-three' =>'No reply 3 / day1',
        'day-two-call-one' =>'No reply 1 / day2',
        'day-two-call-two' =>'No reply 2 / day2',
        'day-two-call-three' =>'No reply 3 / day2',
        'day-three-call-one' =>'No reply 1 / day3',
        'day-three-call-two' =>'No reply 2 / day3',
        'day-three-call-three' =>'No reply 3 / day3',
        'reporter' =>'Reported',
        'annuler' =>'Canceled',
        'wrong-number' =>'Wrong number',
        'confirmer' =>'Confirmed',
        'double' =>'Double',
        'reconfirmer' => 'Reconfirmed'
    ];
    
    public static function orders($request) {
        $orders = Order::query();
        
        $created_from = $request->created_from;
        $created_to = $request->created_to;
        $product_id = $request->product_id;
        $user_id = $request->user_id;
        
        $orders = $orders->when(!!$created_from, fn($q) => $q->whereDate('created_at', '>=', $created_from))
        ->when(!!$created_to, fn($q) => $q->whereDate('created_at', '<=', $created_to))
        ->when($product_id != 'all', fn($q) => $q->whereHas('items', fn($oq) => $oq->where('product_id', $product_id)))
        ->when($user_id != 'all' , fn($q) => $q->where('user_id',$user_id));
        return $orders;
    }
    
    public static function ads($request) {
        $ads = Ads::query();
        
        $created_from = $request->created_from;
        $created_to = $request->created_to;
        $product_id = $request->product_id;
        $user_id = $request->user_id;
        
        $ads = $ads
        ->when(!!$created_from, fn($q) => $q->whereDate('ads_at', '>=', $created_from))
        ->when(!!$created_to, fn($q) => $q->whereDate('ads_at', '<=', $created_to))
        ->when($product_id != 'all', fn($q) => $q->where('product_id', $product_id));
        return $ads;
    }



    public static function admin($request) {
        // $orders = Order::query();
        $ads = Ads::query();

        // $created_from = $request->created_from;
        // $created_to = $request->created_to;
        // $product_id = $request->product_id;
        // $user_id = $request->user_id;
        
        // $orders = $orders->when(!!$created_from, fn($q) => $q->whereDate('created_at', '>=', $created_from))
        // ->when(!!$created_to, fn($q) => $q->whereDate('created_at', '<=', $created_to))
        // ->when($product_id != 'all', fn($q) => $q->whereHas('items', fn($oq) => $oq->where('product_id', $product_id)))
        // ->when($user_id != 'all' , fn($q) => $q->where('user_id',$user_id));
        
        $confirmations = [];
        
        
        $allCount = self::orders($request)->count();
        $all = [
            'id' => 1,
            'title' => 'Total',
            'value' => $allCount,
            'icon' => 'mdi-package',
            'color' => '#6b7280'
        ];
        $confirmations[] = $all;
        
        
        $confirmedCount = self::orders($request)->where('confirmation', 'confirmer')->count();
        $totalCount = self::orders($request)->where('confirmation', '!=', 'double')->where('confirmation', '!=', null)->count();
        $confirmed = [
            'id' => 2,
            'title' => 'Confirmed',
            'value' => $confirmedCount,
            'percentage' => $totalCount > 0  ? ($confirmedCount * 100) / $totalCount : 0,
            'icon' => 'mdi-phone-check',
            'color' => '#10b981'
        ];
        $confirmations[] = $confirmed;
        
        
        $deliveryOrders = self::orders($request)->where('confirmation', 'confirmer')->count();
        $deliveredCount = self::orders($request)->where('confirmation', 'confirmer')->where('delivery', 'livrer')->count();
        $delivered = [
            'id' => 3,
            'title' => 'Delivered',
            'value' => $deliveredCount,
            'percentage' => $deliveryOrders > 0  ? ($deliveredCount * 100) / $deliveryOrders : 0,
            'icon' => 'mdi-truck-check',
            'color' => '#10b981'
        ];
        $confirmations[] = $delivered;
        
        
        $totalSpendPrice = self::ads($request)->sum('amount');
        
        $totalSpend = [
            'id' => 4,
            'title' => 'Total Spend',
            'value' => $totalSpendPrice,
            'icon' => 'mdi-cash-multiple',
            'color' => '#ef4444'
        ];
        $confirmations[] = $totalSpend;
        
        
        $costPerLead = !$totalCount ? 0 : $totalSpendPrice /  $totalCount;
        
        $totalSpend = [
            'id' => 5,
            'title' => 'Cost per lead',
            'value' => number_format($costPerLead,2),
            'icon' => 'mdi-account',
            'color' => '#f97316'
        ];
        $confirmations[] = $totalSpend;
        
        
        $costPerDelivred = !$deliveredCount ? 0 : $totalSpendPrice / $deliveredCount;
        
        $totalSpend = [
            'id' => 6,
            'title' => 'Cost per delivered',
            'value' => number_format($costPerDelivred,2),
            'icon' => 'mdi-truck-delivery-outline',
            'color' => '#f97316'
        ];
        $confirmations[] = $totalSpend;
        
        
        
        $orderIds = self::orders($request)->whereNotIn('confirmation', ['double', 'annuler'])->where([['confirmation', 'confirmer'], ['delivery', 'livrer']])->get()->pluck('id')->values()->toArray();
        $ordersTotalRevenue = self::orders($request)->where([['confirmation', 'confirmer'], ['delivery', 'livrer']])->sum('price');
        $orderItemsTotalRevenue = OrderItem::whereIn('order_id', $orderIds)->sum('price');
        $revenueValue = round($ordersTotalRevenue + $orderItemsTotalRevenue, 2);
        
        $revenue = [
            'id' => 10,
            'title' => 'turnover',
            'value' => $revenueValue,
            'icon' => 'mdi-cash-marker',
            'color' => '#15803d'
        ];
        $confirmations[] = $revenue;
        
        
        $averageOrderValue = !$deliveredCount ? 0 : $revenueValue / $deliveredCount ;
        $totalSpend = [
            'id' => 7,
            'title' => 'Aov',
            'value' => number_format($averageOrderValue ,2),
            'icon' => 'mdi-currency-usd-off',
            'color' => '#f87171'
        ];
        $confirmations[] = $totalSpend;
        
        $totalPriceOfBuyingOrders = self::orders($request)->where([['confirmation', 'confirmer'], ['delivery', 'livrer']])
        ->select(DB::raw('(SELECT SUM(buying_price) FROM order_items JOIN products ON order_items.product_id = products.id WHERE order_items.order_id = orders.id) as buying'))
        ->get()->sum('buying');
        

        $totalPriceOfDeliveryOrders = self::orders($request)->where([['confirmation', 'confirmer'], ['delivery', 'livrer']])
        ->select(DB::raw('(SELECT fee FROM cities JOIN delivery_places ON cities.id = delivery_places.city_id WHERE cities.name = orders.city AND delivery_places.delivery_id = orders.affectation) as delivery_fee'))
        ->get()->sum('delivery_fee');

        $totalCharges = $totalPriceOfBuyingOrders + $totalPriceOfDeliveryOrders + $totalSpendPrice;
    
        $profitPerOrder = !$deliveredCount ? 0 : ($revenueValue - $totalCharges) /  $deliveredCount;
        
        $totalSpend = [
            'id' => 8,
            'title' => 'Profit per order',
            'value' => number_format($profitPerOrder,2),
            'icon' => 'mdi-package-variant-closed',
            'color' => '#16a34a'
        ];
        $confirmations[] = $totalSpend;
        
        
        
        $totalQuantityDelivered = self::orders($request)->where([['confirmation', 'confirmer'], ['delivery', 'livrer']])
        ->select(DB::raw('(SELECT SUM(quantity) FROM order_items  WHERE order_items.order_id = orders.id) as total_quantity'))
        ->get()->sum('total_quantity');
    
        $profitPerUnit = !$totalQuantityDelivered ? 0 : ($revenueValue - $totalCharges) /  $totalQuantityDelivered;

        $totalSpend = [
            'id' => 9,
            'title' => 'Profit per unit',
            'value' => number_format( $profitPerUnit , 2),
            'icon' => 'mdi-currency-usd',
            'color' => '#16a34a'
        ];
        $confirmations[] = $totalSpend;
        
        
        return [ 'analytics' => $confirmations ];
    }



    public static function getPrice($order) {
        if (!$order) return 0;
        $total = array_reduce($order['items']->values()->toArray(), function($sum, $item) {
            return $sum + (!$item['price'] ? 0 : $item['price']);
        }, 0);
            return floatval(!$order['price'] ? 0 : $order['price']) + floatval($total);
    }


    public static function getBuyingPrice($order) {
        if (!$order) return 0;
    
        $totalBuyingPrice = array_reduce($order['items']->values()->toArray(), function($sum, $item) {
            return $sum + (!$item['product']['buying_price'] ? 0 : $item['product']['buying_price']);
        }, 0);
    
        return floatval($totalBuyingPrice);
    }

    public static function findDeliveryFeeForOrder($order)
    {
        if (!$order) return 0;
       
        // Get the city of the order
         $orderCity =  $order->city;
        //  return $order;
        // Get the delivery user associated with the order
         $deliveryUser = $order->delivery_user;
        
        // Get the delivery places associated with the delivery user
        $deliveryPlaces = $deliveryUser->toArray()['delivery_places'];;
        // Find the delivery fee for the order's city
        $deliveryFee = 0; // Initialize the fee to zero
        // return $deliveryPlaces;
        if($deliveryPlaces){
            foreach ($deliveryPlaces as $deliveryPlace) {
                if ($deliveryPlace['city']['name'] == $orderCity) {
                    $deliveryFee = $deliveryPlace['fee'];
                    break; // Stop searching once a matching place is found
                }
            }
        
           
        }
        return $deliveryFee;
    }

    public static function getQuantity($order) {
        if (!$order) return 0;
    
        $getQuantity = array_reduce($order['items']->values()->toArray(), function($sum, $item) {
            return $sum + (!$item['quantity'] ? 0 : $item['quantity']);
        }, 0);
    
        return floatval($getQuantity);
    }
        
    

}
