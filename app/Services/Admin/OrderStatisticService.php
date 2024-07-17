<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Http;

class OrderStatisticService
{


    public static function getOrdersCountByDays($from = null, $to = null, $seller_id = null)
    {
        $from = $from ?? now()->subDays(7)->startOfDay();
        $to = $to ?? now()->endOfDay();

        $query = DB::table('orders')
            ->where('confirmation', '!=', 'double')
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
            (new \DateTime($to))->modify('+1 day')
        );

        $result = $query->get();

        return $result;

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
        $sellers = Role::where('name', 'seller')->first()->users()->where('status', 1)->select('id')->get()->pluck('id')->toArray();

        $result = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->whereIn('orders.user_id', $sellers)
            ->select('users.username', 'orders.confirmation', DB::raw('count(*) as order_count'))
            ->groupBy('users.username', 'orders.confirmation')
            ->get()
            ->groupBy('username')
            ->map(function ($items, $key) {
                return $items->keyBy('confirmation');
            });

        // Convert the result to an array if needed
        $resultArray = $result->toArray();

        return $result;
    }
}
