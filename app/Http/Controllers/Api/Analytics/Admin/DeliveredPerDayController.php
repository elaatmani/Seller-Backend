<?php

namespace App\Http\Controllers\Api\Analytics\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;

class DeliveredPerDayController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $start = Carbon::now()->subDays(7)->startOfDay();

        $end = Carbon::now()->endOfDay();

        $result = DB::table('history')
            ->distinct('trackable_id')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('trackable_type', 'App\\Models\\Order')
            ->whereDate('created_at', '<=', $end)
            ->whereDate('created_at', '>=', $start)
            ->where('fields', 'like', '%"new_value":"livrer"%')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get();

        // Make sure every day in the period is represented
        $dates = collect();
        for ($date = $start; $date->lte($end); $date->addDay()) {
            $dates->put($date->format('Y-m-d'), 0);
        }

        $result->each(function ($item) use ($dates) {
            $dates->put($item->date, $item->count);
        });

        $finalResult = $dates->map(function ($count, $date) {
            return ['date' => $date, 'count' => $count];
        })->values();

        // New today/yesterday query
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $todayStats = DB::table('history')
            ->where('fields', 'like', '%"field":"delivery"%')
            ->where('actor_id', '=', '4')
            ->where(function ($query) use ($today, $yesterday) {
                $query->whereDate('created_at', $today)
                    ->orWhereDate('created_at', $yesterday);
            })
            ->selectRaw('
            SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as treated_today,
            SUM(CASE WHEN DATE(created_at) = ? AND fields LIKE ? THEN 1 ELSE 0 END) as delivered_today,
            SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as treated_yesterday,
            SUM(CASE WHEN DATE(created_at) = ? AND fields LIKE ? THEN 1 ELSE 0 END) as delivered_yesterday
        ', [
                $today->format('Y-m-d'),
                $today->format('Y-m-d'),
                '%"new_value":"livrer"%',
                $yesterday->format('Y-m-d'),
                $yesterday->format('Y-m-d'),
                '%"new_value":"livrer"%'
            ])
            ->first();

        // Handle potential NULL results
        $treatedToday = $todayStats->treated_today ?? 0;
        $deliveredToday = $todayStats->delivered_today ?? 0;
        $deliveryRateToday = $treatedToday > 0 ? round(($deliveredToday / $treatedToday) * 100, 2) : 0;
        $treatedYesterday = $todayStats->treated_yesterday ?? 0;
        $deliveredYesterday = $todayStats->delivered_yesterday ?? 0;
        $deliveryRateYesterday = $treatedYesterday > 0 ? round(($deliveredYesterday / $treatedYesterday) * 100, 2) : 0;

        $diffrence = $deliveredToday - $deliveredYesterday;

        return response()->json([
            'code' => 'SUCCESS',
            'data' => $finalResult,
            'stats' => [
                    'treated_today' => (int)$treatedToday,
                    'delivered_today' => (int)$deliveredToday,
                    'treated_yesterday' => (int)$treatedYesterday,
                    'delivered_yesterday' => (int)$deliveredYesterday,
                    'delivery_rate_today' => $deliveryRateToday,
                    'delivery_rate_yesterday' => $deliveryRateYesterday,
                    'diffrence' => $diffrence
            ]
        ]);
    }
}