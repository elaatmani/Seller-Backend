<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Analytics\Admin\ChartController;
use App\Http\Controllers\Api\Analytics\Admin\OrderCountController;
use App\Http\Controllers\Api\Analytics\Admin\OrderRevenueController;
use App\Http\Controllers\Api\Analytics\Admin\OrdersPerDayController;
use App\Http\Controllers\Api\Analytics\Admin\AgentsRankingController;
use App\Http\Controllers\Api\Analytics\Admin\OrderBySellerController;
use App\Http\Controllers\Api\Analytics\Admin\OrderDeliveryController;
use App\Http\Controllers\Api\Analytics\Admin\AssignedPerDayController;
use App\Http\Controllers\Api\Analytics\Admin\ConfirmedPerDayController;
use App\Http\Controllers\Api\Analytics\Admin\DeliveredPerDayController;
use App\Http\Controllers\Api\Analytics\Admin\OrderConfirmationController;
use App\Http\Controllers\Api\Analytics\Admin\ProductPerformanceController;

Route::get('/', function() {
    return response()->json([
        'message' => 'Hello from analytics.'
    ]);
});

Route::get('/chart-confirmation', [ChartController::class,'chartConfirmationn']);
Route::get('/orders-count-by-days', OrderCountController::class);
Route::get('/confirmations-count', OrderConfirmationController::class);
Route::get('/deliveries-count', OrderDeliveryController::class);
Route::get('/revenue', [OrderRevenueController::class, 'index']);
Route::get('/profit', [OrderRevenueController::class, 'profit']);
Route::get('/orders-by-sellers', OrderBySellerController::class);
Route::get('/products-by-performance', ProductPerformanceController::class);

Route::get('confirmed-per-day', ConfirmedPerDayController::class);
Route::get('delivered-per-day', DeliveredPerDayController::class);
Route::get('orders-per-day', OrdersPerDayController::class);

Route::get('assigned-per-day', AssignedPerDayController::class);
        Route::get('agents-ranking', AgentsRankingController::class);