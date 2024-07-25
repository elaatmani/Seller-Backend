<?php

namespace App\Http\Controllers\Api\Analytics\Admin;

use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\OrderStatisticService;
use Illuminate\Support\Facades\DB;

class OrderCountController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $service = new OrderStatisticService();
        $from = $request->query('from');
        $to = $request->query('to');
        $sellers = $request->query('sellers', null);
    
        if($from) {
            $from = Carbon::parse($from);
        }

        if($to) {
            $to = Carbon::parse($to);
            $to = $to->endOfDay();
        }

        $results = $service->getOrdersCountByDays($from, $to, $sellers);
        $count = DB::table(
            'orders'
        )->when($from, function ($query) use ($from) {
            return $query->whereDate('orders.created_at', '>=', $from);
        })
        ->when($to, function ($query) use ($to) {
            return $query->whereDate('orders.created_at', '<=', $to);
        })
        ->when($sellers, function ($query) use ($sellers) {
            return $query->whereIn('orders.user_id', $sellers);
        })
        ->count();

        return response()->json([
            'code' => 'SUCCESS',
            'data' => $results,
            'count' => $count,
        ]);

    }
}
