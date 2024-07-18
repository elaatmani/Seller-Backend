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


    public static function getOrdersCountByDays($from = null, $to = null, $seller_id = null)
    {
        $from = $from ?? now()->subDays(7)->startOfDay();
        $to = $to ?? now()->endOfDay();

        $query = DB::table('orders')
            // ->where('confirmation', '!=', 'double')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$from, $to])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc');

        if ($seller_id) {
            $query->where('user_id', $seller_id);
        }

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


    public static function getConfirmationsCount()
    {
        $confirmations = DB::table('orders')
            ->select('confirmation', DB::raw('count(*) as count'))
            ->groupBy('confirmation')
            ->get();

        return $confirmations;
    }

    public static function getDeliveriesCount()
    {
        $delivery = DB::table('orders')
            ->whereIn('confirmation', ['confirmer', 'change', 'refund'])
            ->select('delivery', DB::raw('count(*) as count'))
            ->groupBy('delivery')
            ->get();

        return $delivery;
    }

    public static function getOrdersBySellers($from = null, $to = null, $seller_id = null)
    {
        // Step 1: Get all seller IDs
        $sellerIds = Role::where('name', 'seller')->first()->users()->where('status', 1)->pluck('id')->toArray();

        // Step 2: Subquery to get the total orders count per seller
        $subQuery = DB::table('orders')
            ->select('user_id', DB::raw('COUNT(*) as total_orders'))
            ->groupBy('user_id');

        // Step 3: Main query to join with users and subquery, and group by confirmation
        $result = DB::table('orders')
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
}
