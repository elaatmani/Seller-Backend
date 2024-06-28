<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Services\NewFactorisationService;
use App\Http\Controllers\Api\Seller\AffiliateController;
use App\Http\Controllers\Api\Admin\AffiliateProductController;


Route::get('/', function() {
    return response()->json([
        'message' => 'Hello from affiliate.'
    ]);
});


Route::group(['prefix' => 'products'], function() {
    Route::get('/', [AffiliateProductController::class, 'index']);
    Route::get('/imported', [AffiliateProductController::class, 'imported']);
    Route::get('/wishlisted', [AffiliateProductController::class, 'wishlisted']);
    Route::get('/{id}', [AffiliateProductController::class, 'show']);
    Route::post('/', [AffiliateProductController::class, 'store']);
});

Route::post('/import', [AffiliateController::class, 'import']);
Route::post('/unimport', [AffiliateController::class, 'unimport']);

Route::post('/wishlist', [AffiliateController::class, 'wishlist']);
Route::post('/unwishlist', [AffiliateController::class, 'unwishlist']);