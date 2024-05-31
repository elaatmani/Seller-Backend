<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Services\NewFactorisationService;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;


Route::get('/', function() {
    return response()->json([
        'message' => 'Hello from debug.'
    ]);
});


Route::get('/fix-invoices', function() {

    $ids = [];

    $orders = Order::whereIn('id', $ids)->get();

    foreach($orders as $order) {

        $invoice = NewFactorisationService::getActiveSellerInvoice($order->user_id);

        $order->seller_factorisation_id = $invoice->id;
        $order->delivery = 'livrer';
        $order->is_paid_to_seller = false;
        $order->is_delivered = true;
        $order->is_paid_by_delivery = true;
        $order->save();
    }

    return response()->json([
        'message' => 'Hello from debug.'
    ]);
});


Route::get('/fix-paid-with-shipping', function() {

    $ids = [];

    $orders = Order::whereIn('id', $ids)->get();

    foreach($orders as $order) {

        $invoice = NewFactorisationService::getActiveSellerInvoice($order->user_id);

        $order->seller_factorisation_id = $invoice->id;
        $order->delivery = 'livrer';
        $order->is_paid_to_seller = false;
        $order->is_delivered = true;
        $order->is_paid_by_delivery = true;
        $order->save();
    }

    return response()->json([
        'message' => 'Hello from debug.'
    ]);
});
