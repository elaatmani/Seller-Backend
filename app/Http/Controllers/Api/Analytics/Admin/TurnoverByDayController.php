<?php

namespace App\Http\Controllers\Api\Analytics\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TurnoverByDayController extends Controller
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

        // Subquery to get latest delivery date for each order
        $subQuery = DB::table('history')
            ->select('trackable_id', DB::raw('MAX(created_at) as delivery_date'))
            ->where('trackable_type', 'LIKE', '%Order%')
            ->where('fields', 'LIKE', '%"new_value":"livrer"%')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('trackable_id');

        // Main query to calculate daily totals
        $result = DB::table(DB::raw("({$subQuery->toSql()}) as deliveries"))
            ->mergeBindings($subQuery)
            ->select(DB::raw('DATE(deliveries.delivery_date) as date'), DB::raw('SUM(order_items.price) as total'))
            ->join('order_items', 'deliveries.trackable_id', '=', 'order_items.order_id')
            ->groupBy(DB::raw('DATE(deliveries.delivery_date)'))
            ->get();

        // Create date range with 0 values
        $dates = collect();
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dates->put($date->format('Y-m-d'), 0);
        }

        // Merge query results with date range
        $result->each(function ($item) use ($dates) {
            $dates->put($item->date, $item->total);
        });

        // Format final result
        $finalResult = $dates->map(function ($total, $date) {
            return ['date' => $date, 'total' => $total];
        })->values();

        return response()->json([
            'code' => 'SUCCESS',
            'data' => $finalResult
        ]);
    }
}
