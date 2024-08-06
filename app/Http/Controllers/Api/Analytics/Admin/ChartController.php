<?php

namespace App\Http\Controllers\Api\Analytics\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\StatisticsService;


class ChartController extends Controller
{
    public function chartConfirmationn(Request $request)
    {
        return StatisticsService::chartConfirmation($request);
    }
}
