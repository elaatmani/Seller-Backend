<?php

namespace App\Http\Controllers\Api\Analytics\Admin;

use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\OrderStatisticService;

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
        $count = Order::count();

        return response()->json([
            'code' => 'SUCCESS',
            'data' => $results,
            'count' => $count,
        ]);

    }
}
