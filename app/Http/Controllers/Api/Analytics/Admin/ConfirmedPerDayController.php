<?php

namespace App\Http\Controllers\Api\Analytics\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;

class ConfirmedPerDayController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // Existing 7-day query
        $start = Carbon::now()->subDays(7)->startOfDay();
        $end = Carbon::now()->endOfDay();

        $result = DB::table('history')
            ->distinct('trackable_id')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('trackable_type', 'App\\Models\\Order')
            ->whereDate('created_at', '<=', $end)
            ->whereDate('created_at', '>=', $start)
            ->where('fields', 'like', '%"new_value":"confirmer"%')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get();

        // Fill missing dates
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
            ->where('fields', 'like', '%"field":"confirmation"%')
            ->where(function ($query) use ($today, $yesterday) {
                $query->whereDate('created_at', $today)
                    ->orWhereDate('created_at', $yesterday);
            })
            ->selectRaw('
            SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as treated_today,
            SUM(CASE WHEN DATE(created_at) = ? AND fields LIKE ? THEN 1 ELSE 0 END) as confirmed_today,
            SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as treated_yesterday,
            SUM(CASE WHEN DATE(created_at) = ? AND fields LIKE ? THEN 1 ELSE 0 END) as confirmed_yesterday
        ', [
                $today->format('Y-m-d'),
                $today->format('Y-m-d'),
                '%"new_value":"confirmer"%',
                $yesterday->format('Y-m-d'),
                $yesterday->format('Y-m-d'),
                '%"new_value":"confirmer"%'
            ])
            ->first();

        // Handle potential NULL results
        $treatedToday = $todayStats->treated_today ?? 0;
        $confirmedToday = $todayStats->confirmed_today ?? 0;
        $confirmationRateToday = $treatedToday > 0 ? round(($confirmedToday / $treatedToday) * 100, 2) : 0;
        $treatedYesterday = $todayStats->treated_yesterday ?? 0;
        $confirmedYesterday = $todayStats->confirmed_yesterday ?? 0;
        $confirmationRateYesterday = $treatedYesterday > 0 ? round(($confirmedYesterday / $treatedYesterday) * 100, 2) : 0;

        $diffrence = $confirmedToday - $confirmedYesterday;

        return response()->json([
            'code' => 'SUCCESS',
            'data' => $finalResult,
            'stats' => [
                    'treated_today' => (int)$treatedToday,
                    'confirmed_today' => (int)$confirmedToday,
                    'treated_yesterday' => (int)$treatedYesterday,
                    'confirmed_yesterday' => (int)$confirmedYesterday,
                    'confirmation_rate_today' => $confirmationRateToday,
                    'confirmation_rate_yesterday' => $confirmationRateYesterday,
                    'diffrence' => $diffrence
            ]
        ]);
    }
}
