<?php

namespace App\Http\Controllers\Api\Analytics\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\OrderStatisticService;

class OrderBySellerController extends Controller
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

        $results = $service->getOrdersBySellers();

        return response()->json([
            'code' => 'SUCCESS',
            'data' => $results
        ]);
    }
}
