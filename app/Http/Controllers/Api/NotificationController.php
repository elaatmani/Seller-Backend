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
     * Notification admin the specified resource from storage.
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
            
            
            $reportedOrderAgente = Order::where([
                ['confirmation', '=', 'reporter'],
                ['reported_agente_date', '=', now()->toDateString()] // add condition for current date
            ])->get();

            $reportedOrderDelivery = Order::where([
                ['delivery', '=', 'reporter'],
                ['reported_delivery_date', '=', now()->toDateString()] // add condition for current date
            ])->get();

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


    /**
     * Notification Agente the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function agenteNotifications(Request $request){
        try {
           
            if (!$request->user()->hasRole('agente')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See this kind Of Notification',
                    ],
                    405
                );
            }
            
            $reportedOrderAgente = Order::where([
                ['agente_id' , '=' , $request->user()->id],
                ['confirmation', '=', 'reporter'],
                ['reported_agente_date', '=', now()->toDateString()] // add condition for current date
            ])->get();

                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => [
                            'reportedOrderAgente' => $reportedOrderAgente,
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



    /**
     * Notification Agente the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function deliveryNotifications(Request $request){
        try {
           
            if (!$request->user()->hasRole('delivery')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See this kind Of Notification',
                    ],
                    405
                );
            }
            
            $reportedOrderDelivery = Order::where([
                ['affectation' , '=' , $request->user()->id],
                ['delivery', '=', 'reporter'],
                ['reported_delivery_date', '=', now()->toDateString()] // add condition for current date
            ])->get();

                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => [
                            'reportedOrderDelivery' => $reportedOrderDelivery,
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
