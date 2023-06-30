<?php

use App\Http\Controllers\Api\Public\ClientController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
use Illuminate\Http\Request;


Route::post('order/delivery', [ClientController::class, 'updateDelivery'])->middleware('bearer:delivery-update');


