<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;


Route::get('/', function() {
    return response()->json([
        'message' => 'Hello from debug.'
    ]);
});
