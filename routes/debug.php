<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\UserProduct;
use App\Services\Admin\FinanceStatisticService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Services\NewFactorisationService;


Route::get('/', function() {
    return response()->json([
        'message' => 'Hello from debug.'
    ]); 
});

Route::get('test',  function() {
    return FinanceStatisticService::getRevenue();
});