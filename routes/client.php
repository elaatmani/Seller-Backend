<?php

use App\Http\Controllers\Api\Public\ClientController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
use Illuminate\Http\Request;


Route::post('order/delivery', [ClientController::class, 'updateDelivery'])->middleware('bearer:delivery-update');
Route::post('order/delivery/multiple', [ClientController::class, 'updateMultipleDelivery'])->middleware('bearer:delivery-update');

Route::post('getToken', function() {
    $token = User::find(3)->createToken('API TOKEN', ["delivery:update"])->plainTextToken;
    return $token;
});


