<?php

use App\Http\Controllers\Api\Analytics\Admin\OrderBySellerController;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Analytics\Admin\OrderCountController;
use App\Http\Controllers\Api\Analytics\Admin\OrderRevenueController;
use App\Http\Controllers\Api\Analytics\Admin\OrderDeliveryController;
use App\Http\Controllers\Api\Analytics\Admin\OrderConfirmationController;
use App\Http\Controllers\Api\Analytics\Admin\ProductPerformanceController;

Route::get('/', function() {
    return response()->json([
        'message' => 'Hello from analytics.'
    ]);
});

Route::get('/orders-count-by-days', OrderCountController::class);
Route::get('/confirmations-count', OrderConfirmationController::class);
Route::get('/deliveries-count', OrderDeliveryController::class);
Route::get('/revenue', [OrderRevenueController::class, 'index']);
Route::get('/orders-by-sellers', OrderBySellerController::class);
Route::get('/products-by-performance', ProductPerformanceController::class);