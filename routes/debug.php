<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\UserProduct;
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
    return UserProduct::where([
        'product_id' => 54,
        'user_id' => 5,
    ])->exists();
});