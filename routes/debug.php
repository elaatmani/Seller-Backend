<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Services\NewFactorisationService;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;


Route::get('/', function() {
    return response()->json([
        'message' => 'Hello from debug.'
    ]); 
});

Route::get('test',  function() {
    return Product::where('id', 48)->first()->affiliate_users;
    return ;
});