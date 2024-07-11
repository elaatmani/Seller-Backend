<?php

use App\Http\Controllers\Api\Analytics\Admin\OrderCountController;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;


Route::get('/', function() {
    return response()->json([
        'message' => 'Hello from analytics.'
    ]);
});

Route::get('/orders-count-by-days', OrderCountController::class);
