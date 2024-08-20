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

        $orders = DB::table('orders')
            ->join('factorisations', 'factorisations.id', '=', 'orders.seller_factorisation_id')
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
                DB::raw('SUM(CASE WHEN orders.delivery = "paid" THEN 1 ELSE 0 END) as orders_count'),
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

        $factorisation_fees = DB::table('factorisation_fees')
            ->join('factorisations', 'factorisation_fees.factorisation_id', '=', 'factorisations.id')
            ->when($seller_ids, function ($query) use ($seller_ids) {
                $query->whereIn('factorisations.user_id', $seller_ids);
            })
            ->when($from, function ($query) use ($from) {
                $query->whereDate('factorisations.paid_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('factorisations.paid_at', '<=', $to);
            })
            ->where([
                'close' => 1,
                'paid' => 1
            ])->sum('factorisation_fees.feeprice');

        $fees = $orders->shipping_fees + $turnover * 0.04 + $factorisation_fees;

        $net_paid = $turnover - $fees;

        return [
            'turnover' => $turnover,
            'orders' => $orders,
            'aov' => $orders->orders_count > 0 ? $turnover / $orders->orders_count : 0,
            'net_paid' => $net_paid,
            'total_fees' => $fees,
        ];
    }

    public static function getProfit($from = null, $to = null, $seller_ids = null)
    {
        $profitByCreatedAt = self::profitByCreatedAt($from, $to, $seller_ids);
        $profitByDeliveredAt = self::profitByDeliveredAt($from, $to, $seller_ids);

        return [
            'profit_by_created_at' => $profitByCreatedAt,
            'profit_by_delivered_at' => $profitByDeliveredAt,
        ];
    }

    public static function profitByCreatedAt($from = null, $to = null, $seller_ids = null) {
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
                DB::raw('SUM(CASE WHEN orders.delivery = "paid" THEN 1 ELSE 0 END) as orders_count'),
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

        $sourcings = DB::table('sourcings')
            ->where('quotation_status', 'confirmed')
            ->where('buying_price', '>', 0)
            ->when($seller_ids && count($seller_ids), function ($query) use ($seller_ids) {
                $query->whereIn('sourcings.user_id', $seller_ids);
            })
            ->when($from, function ($query) use ($from) {
                $query->whereDate('sourcings.created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('sourcings.created_at', '<=', $to);
            })
            ->select(
                DB::raw('SUM((cost_per_unit - buying_price) * estimated_quantity) as profit')    
            )
            ->first()->profit;

        $codfees = ($turnover * 0.04);
        $fees = ($orders->variant_fees + $orders->inside_b + $orders->outside_b);

        $profit = $orders->shipping_fees + $codfees - $fees;
        $profitPerOrder = $profit > 0 ? $profit / $orders->orders_count : 0;

        return [
            'turnover' => $turnover,
            'cod_fees' => ($turnover * 0.04),
            'orders' => $orders,
            'profit' => $profit,
            'fees' => $fees,
            'profit_per_order' => $profitPerOrder,
            'sourcings_profit' => $sourcings,
        ];
    }

    public static function profitByDeliveredAt($from = null, $to = null, $seller_ids = null) {
        $ids = DB::table('history')
            ->where('trackable_type', 'App\\Models\\Order')
            ->where('fields', 'like', '%new_value":"livrer"%')
            ->when($from, function ($query) use ($from) {
                $query->whereDate('created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('created_at', '<=', $to);
            })->select('trackable_id')->get()->pluck('trackable_id')->toArray();



        $orders = DB::table('orders')
            ->where('orders.confirmation', 'confirmer')
            ->whereIn('orders.delivery', ['livrer', 'paid'])
            ->whereIn('orders.id', $ids)
            ->when($seller_ids && count($seller_ids), function ($query) use ($seller_ids) {
                $query->whereIn('orders.user_id', $seller_ids);
            })
            ->select(
                DB::raw('SUM(CASE WHEN orders.delivery = "paid" THEN 1 ELSE 0 END) as orders_count'),
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
            ->whereIn('orders.delivery', ['livrer', 'paid'])
            ->whereIn('orders.id', $ids)
            ->when($seller_ids && count($seller_ids), function ($query) use ($seller_ids) {
                $query->whereIn('orders.user_id', $seller_ids);
            })
            ->sum('order_items.price');

        $sourcings = DB::table('sourcings')
            ->where('quotation_status', 'confirmed')
            ->where('buying_price', '>', 0)
            ->when($seller_ids && count($seller_ids), function ($query) use ($seller_ids) {
                $query->whereIn('sourcings.user_id', $seller_ids);
            })
            ->when($from, function ($query) use ($from) {
                $query->whereDate('sourcings.created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('sourcings.created_at', '<=', $to);
            })
            ->select(
                DB::raw('SUM((cost_per_unit - buying_price) * estimated_quantity) as profit')    
            )
            ->first()->profit;

        $codfees = ($turnover * 0.04);
        $fees = ($orders->variant_fees + $orders->inside_b + $orders->outside_b);

        $profit = $orders->shipping_fees + $codfees - $fees;
        $profitPerOrder = $profit > 0 ? $profit / $orders->orders_count : 0;

        return [
            'turnover' => $turnover,
            'cod_fees' => ($turnover * 0.04),
            'orders' => $orders,
            'profit' => $profit,
            'fees' => $fees,
            'profit_per_order' => $profitPerOrder,
            'sourcings_profit' => $sourcings,
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
