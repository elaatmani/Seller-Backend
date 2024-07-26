<?php

namespace App\Http\Controllers\Api\Analytics\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\FinanceStatisticService;

class OrderRevenueController extends Controller
{
    public function index(Request $request)
    {
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
        $revenue = FinanceStatisticService::getRevenue($from, $to, $sellers);
        
        return response()->json([
            'revenue' => $revenue
        ]);
    }
}
