<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\DeliveryPlace;
use App\Models\Factorisation;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DashboardController extends Controller
{

    public function delivery() {
        try {

            $orders = Order::where('affectation', auth()->id())->get();
            $cities = DeliveryPlace::where('delivery_id', auth()->id())->with('city')->get();

            return response()->json([
                'status' => true,
                'code' => 'SUCCESS',
                'data' => [
                    'orders' => $orders,
                    'cities' => $cities
                ]
            ]);

        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'code' => 'SERVER_ERROR',
                    'message' => $th->getMessage()
                ],
                500
            );
        }
    }

    public function agente() {
        try {

            $orders = Order::where('agente_id', auth()->id())->get();

            return response()->json([
                'status' => true,
                'code' => 'SUCCESS',
                'data' => [
                    'orders' => $orders,
                ]
            ]);

        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'code' => 'SERVER_ERROR',
                    'message' => $th->getMessage()
                ],
                500
            );
        }
    }
}
