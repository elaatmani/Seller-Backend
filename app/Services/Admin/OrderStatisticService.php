<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderStatisticService
{


    public static function getOrdersCountByDays($from = null, $to = null, $seller_ids = null)
    {
        $from = $from ?? now()->subDays(7)->startOfDay();
        $to = $to ?? now()->endOfDay();

        $query = DB::table('orders')
            // ->where('confirmation', '!=', 'double')
            ->when($from, function ($query) use ($from) {
                $query->whereDate('orders.created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('orders.created_at', '<=', $to);
            })
            ->when($seller_ids, function ($query) use ($seller_ids) {
                $query->whereIn('orders.user_id', $seller_ids);
            })
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$from, $to])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc');

        // Generate an array of dates in the range
        $period = new \DatePeriod(
            new \DateTime($from),
            new \DateInterval('P1D'),
            (new \DateTime($to))->modify('+0 day')
        );

        $result = $query->get();


        $dateCounts = [];
        foreach ($result as $result) {
            $dateCounts[$result->date] = $result->count;
        }

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            if (!isset($dateCounts[$dateStr])) {
                $dateCounts[$dateStr] = 0;
            }
        }

        // Sort the dates in ascending order
        ksort($dateCounts);

        // Prepare the final result
        $finalResult = [];
        foreach ($dateCounts as $date => $count) {
            $finalResult[] = (object) ['date' => $date, 'count' => $count];
        }

        return $finalResult;
    }


    public static function getConfirmationsCount($from = null, $to = null, $seller_ids)
    {
        $confirmations = DB::table('orders')
            ->when($from, function ($query) use ($from) {
                $query->whereDate('orders.created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('orders.created_at', '<=', $to);
            })
            ->when($seller_ids, function ($query) use ($seller_ids) {
                $query->whereIn('orders.user_id', $seller_ids);
            })
            ->select('confirmation', DB::raw('count(*) as count'))
            ->groupBy('confirmation')
            ->get();

        return $confirmations;
    }

    public static function getDeliveriesCount($from = null, $to = null, $seller_ids)
    {
        $delivery = DB::table('orders')
            ->when($from, function ($query) use ($from) {
                $query->whereDate('orders.created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('orders.created_at', '<=', $to);
            })
            ->when($seller_ids, function ($query) use ($seller_ids) {
                $query->whereIn('orders.user_id', $seller_ids);
            })
            ->whereIn('confirmation', ['confirmer', 'change', 'refund'])
            ->select('delivery', DB::raw('count(*) as count'))
            ->groupBy('delivery')
            ->get();

        return $delivery;
    }

    public static function getOrdersBySellers($from = null, $to = null, $seller_ids = null)
    {
        // Step 1: Get all seller IDs
        $sellerIds = Role::where('name', 'seller')->first()->users()->where('status', 1)
            ->when($seller_ids, function ($query) use ($seller_ids) {
                $query->whereIn('id', $seller_ids);
            })
            ->pluck('id')->toArray();

        // Step 2: Subquery to get the total orders count per seller
        $subQuery = DB::table('orders')
            ->select('user_id', DB::raw('COUNT(*) as total_orders'))
            ->when($from, function ($query) use ($from) {
                $query->whereDate('orders.created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('orders.created_at', '<=', $to);
            })
            ->groupBy('user_id');

        // Step 3: Main query to join with users and subquery, and group by confirmation
        $result = DB::table('orders')
            ->when($from, function ($query) use ($from) {
                $query->whereDate('orders.created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('orders.created_at', '<=', $to);
            })
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->joinSub($subQuery, 'total_orders', function ($join) {
                $join->on('orders.user_id', '=', 'total_orders.user_id');
            })
            ->whereIn('orders.user_id', $sellerIds)
            ->select('users.username', 'orders.confirmation', DB::raw('count(orders.id) as order_count'), 'total_orders.total_orders')
            ->groupBy('users.username', 'orders.confirmation', 'total_orders.total_orders')
            ->orderBy('total_orders', request()->input('order_by', 'high') == 'high' ? 'desc' : 'asc')
            ->get()
            ->groupBy('username')
            ->map(function ($items, $key) {
                return $items->keyBy('confirmation');
            });



        // Return the result
        return new LengthAwarePaginator($result->forPage(request()->input('page', 1), request()->input('per_page', 10)), $result->count(), request()->input('per_page', 10), request()->input('page', 1));
    }

    public static function getProductsPerformance($from = null, $to = null, $seller_ids = null)
    {

        $result = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.confirmation', 'confirmer')
            ->whereIn('orders.delivery', ['paid', 'livrer'])

            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->when($from, function ($query) use ($from) {
                $query->whereDate('order_items.created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('order_items.created_at', '<=', $to);
            })
            ->when($seller_ids, function ($query) use ($seller_ids) {
                $query->whereIn('products.user_id', $seller_ids);
            })
            ->select(
                'products.name',
                'order_items.product_id as product',
                DB::raw('count(order_items.order_id) as count_orders'),
                DB::raw('sum(order_items.quantity) as total_quantity')
            )
            ->groupBy('products.name', 'order_items.product_id')
            ->orderBy('count_orders', request()->input('order_by', 'high') == 'high' ? 'desc' : 'asc')
            ->get();

        // Convert collection to a simple array
        $resultArray = $result->toArray();

        // Pagination parameters
        $page = request()->input('page', 1);
        $perPage = request()->input('per_page', 10);
        $total = count($resultArray);

        // Create a new LengthAwarePaginator instance
        $paginator = new LengthAwarePaginator(
            array_slice($resultArray, ($page - 1) * $perPage, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        return $paginator;
    }
}
