<?php

namespace App\Http\Controllers\Api\Analytics\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\FinanceStatisticService;
use Illuminate\Http\Request;

class OrderRevenueController extends Controller
{
    public function index(Request $request)
    {
        $revenue = FinanceStatisticService::getRevenue();
        return response()->json([
            'revenue' => $revenue
        ]);
    }
}
