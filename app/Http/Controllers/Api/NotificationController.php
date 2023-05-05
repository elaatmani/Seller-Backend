<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{

     /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function notifications(){
        try {
            $stockAlert = Product::whereHas('variations', function ($query) {
                $query->where('quantity', '<=', DB::raw('stockAlert'));
            })->with('variations')->get();
            
            $reportedSale = Order::where(function ($query) {
                $query->where([
                        ['confirmation', '=', 'reporter'],
                        ['reported_agente_date', '=', now()->toDateString()] // add condition for current date
                    ])
                    ->orWhere([
                        ['delivery', '=', 'reporter'],
                        ['reported_delivery_date', '=', now()->toDateString()] // add condition for current date
                    ]);
            })->get();
            
            
            $reportedOrderAgente = Order::where('confirmation','reporter')->get();

            $reportedOrderDelivery = Order::where('delivery','reporter')->get();

                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => [
                            'productStockAlert' => $stockAlert,
                            'reportedSale' => $reportedSale,
                            'reportedOrderAgente' => $reportedOrderAgente,
                            'reportedOrderDelivery' => $reportedOrderDelivery
                        ],
                    ],
                    200
                );
           
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
