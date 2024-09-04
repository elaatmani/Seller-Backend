<?php

namespace App\Http\Controllers\Api\Analytics\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;

class OrdersPerDayController extends Controller
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

        $result = DB::table('orders')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->whereDate('created_at', '<=', $end)
            ->whereDate('created_at', '>=', $start)
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

        return response()->json([
            'code' => 'SUCCESS',
            'data' => $finalResult
        ]);
    }
}