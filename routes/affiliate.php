<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Services\NewFactorisationService;
use App\Http\Controllers\Api\Admin\AffiliateProductController;


Route::get('/', function() {
    return response()->json([
        'message' => 'Hello from affiliate.'
    ]);
});


Route::group(['prefix' => 'products'], function() {
    Route::get('/', [AffiliateProductController::class, 'index']);
    Route::get('/{id}', [AffiliateProductController::class, 'show']);
    Route::post('/', [AffiliateProductController::class, 'store']);
});
