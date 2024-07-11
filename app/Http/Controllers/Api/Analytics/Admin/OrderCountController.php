<?php

namespace App\Http\Controllers\Api\Analytics\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Admin\OrderStatisticService;
use Illuminate\Http\Request;

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

        $results = $service->getOrdersCountByDays();
        $count = Order::count();

        return response()->json([
            'code' => 'SUCCESS',
            'data' => $results,
            'count' => $count,
        ]);

    }
}
