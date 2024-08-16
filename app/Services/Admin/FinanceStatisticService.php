<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Http;

class FinanceStatisticService
{

    public static function getRevenue($from = null, $to = null, $seller_ids = null)
    {
        $ids = [];
        if ($from || $to) {
            $ids = DB::table('factorisations')
                ->when($from, function ($query) use ($from) {
                    $query->whereDate('paid_at', '>=', $from);
                })
                ->when($to, function ($query) use ($to) {
                    $query->whereDate('paid_at', '<=', $to);
                })
                ->select('id')->get()->pluck('id')->values()->toArray();
        }

        $result = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.confirmation', '=', 'confirmer')
            ->when($seller_ids, function ($query) use ($seller_ids) {
                $query->whereIn('orders.user_id', $seller_ids);
            })
            ->when($from || $to, function ($query) use ($ids) {
                $query->whereIn('orders.seller_factorisation_id', $ids);
            })
            ->when($from, function ($query) use ($from) {
                $query->whereDate('orders.created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('orders.created_at', '<=', $to);
            })
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
            ->when($seller_ids, function ($query) use ($seller_ids) {
                $query->whereIn('factorisations.user_id', $seller_ids);
            })
            ->when($from || $to, function ($query) use ($ids) {
                $query->whereIn('factorisations.id', $ids);
            })
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

    public static function getProfit($from = null, $to = null, $seller_ids = null)
    {
        $orders = DB::table('orders')
            ->where('orders.confirmation', 'confirmer')
            ->where('orders.delivery', 'paid')
            ->when($from, function ($query) use ($from) {
                $query->whereDate('orders.created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('orders.created_at', '<=', $to);
            })
            ->when($seller_ids && count($seller_ids), function ($query) use ($seller_ids) {
                $query->whereIn('orders.user_id', $seller_ids);
            })
            ->select(
                DB::raw('SUM(CASE WHEN orders.delivery = "paid" THEN 1 ELSE 0 END) as paid_count'),
                DB::raw('SUM(CASE WHEN orders.delivery = "paid" THEN 8 ELSE 0 END) as shipping_fees'),
                DB::raw('SUM(CASE WHEN orders.delivery = "paid" THEN 2 ELSE 0 END) as variant_fees'),
                DB::raw('SUM(CASE WHEN orders.city like "%inside%" THEN 1.80 ELSE 0 END) as inside_b'),
                DB::raw('SUM(CASE WHEN orders.city like "%inside%" THEN 1 ELSE 0 END) as inside_b_count'),
                DB::raw('SUM(CASE WHEN orders.city not like "%inside%" THEN 2.60 ELSE 0 END) as outside_b'),
                DB::raw('SUM(CASE WHEN orders.city not like "%inside%" THEN 1 ELSE 0 END) as outside_b_count'),

            )
            ->first();

        $turnover = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.confirmation', 'confirmer')
            ->where('orders.delivery', 'paid')
            ->when($from, function ($query) use ($from) {
                $query->whereDate('orders.created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('orders.created_at', '<=', $to);
            })
            ->when($seller_ids && count($seller_ids), function ($query) use ($seller_ids) {
                $query->whereIn('orders.user_id', $seller_ids);
            })
            ->sum('order_items.price');

        $codfees = ($turnover * 0.04);
        $fees = ($orders->variant_fees + $orders->inside_b + $orders->outside_b);

        $profit = $orders->shipping_fees + $codfees - $fees;


        return [
            'turnover' => $turnover,
            'cod_fees' => ($turnover * 0.04),
            'orders' => $orders,
            'profit' => $profit,
            'fees' => $fees,
            'profit_per_order' => $profit / $orders->paid_count
        ];
    }


    public static function getAverageOrderValue($from = null, $to = null, $seller_ids = null)
    {
        $ordersCount = DB::table('orders')
            ->where('orders.confirmation', '=', 'confirmer')
            ->when($seller_ids, function ($query) use ($seller_ids) {
                $query->whereIn('orders.user_id', $seller_ids);
            })
            ->when($from, function ($query) use ($from) {
                $query->whereDate('orders.created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('orders.created_at', '<=', $to);
            })
            ->whereIn('orders.delivery', ['paid', 'cleared', 'livrer'])
            ->count();

        $revenue = self::getRevenue();
        $totalRevenue = $revenue->sum_livrer + $revenue->sum_paid;

        return $totalRevenue > 0 ? round($totalRevenue / $ordersCount, 2) : 0;
    }

    public static function getDeliveredOrdersRevenue($from = null, $to = null, $seller_ids = null)
    {

        $result = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->when($seller_ids, function ($query) use ($seller_ids) {
                $query->whereIn('orders.user_id', $seller_ids);
            })
            ->when($from, function ($query) use ($from) {
                $query->whereDate('orders_items.created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('orders_items.created_at', '<=', $to);
            })
            ->where('orders.confirmation', '=', 'confirmer')
            ->whereIn('orders.delivery', ['livrer'])
            ->sum('order_items.price');

        return $result;
    }

    public static function getPaidOrdersRevenue($from = null, $to = null, $seller_ids = null)
    {

        // DB::table('factorisation')->whereBetween('paid_at', [$from, $to])->select('id')->get()->values()->toArray();
        $result = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->when($seller_ids, function ($query) use ($seller_ids) {
                $query->whereIn('orders.user_id', $seller_ids);
            })
            ->when($from, function ($query) use ($from) {
                $query->whereDate('orders_items.created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('orders_items.created_at', '<=', $to);
            })
            ->where('orders.confirmation', '=', 'confirmer')
            ->whereIn('orders.delivery', ['paid', 'cleared'])
            ->sum('order_items.price');

        return $result;
    }
}
