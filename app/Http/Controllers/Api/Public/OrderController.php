<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductAgente;
use Illuminate\Http\Request;

class OrderController extends Controller
{


    /**
     * Display order not confirmed yet.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function orderToConfirme(Request $request)
    {
        if (!$request->user()->can('order_show')) {
            return response()->json(
                [
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To See Orders',
                ],
                405
            );
        }



        $order = Order::where([['agente_id', $request->user()->id], ['confirmation', null]])->get();

        if (count($order) > 0) {
            return response()->json(
                [
                    'status' => true,
                    'code' => 'SUCCESS',
                    'data' => [
                        'orders' => $order
                    ]
                ],
                200
            );
        }

        return response()->json(
            [
                'status' => true,
                'code' => 'SUCCESS',
                'data' => 'Add New One !'
            ],
            200
        );
    }


    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('order_show')) {
            return response()->json(
                [
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To See Orders',
                ],
                405
            );
        }



        $orders = Order::where([['agente_id', $request->user()->id], ['confirmation', '!=', 'confirme']])->get();

        if (count($orders) > 0) {
            return response()->json(
                [
                    'status' => true,
                    'code' => 'SUCCESS',
                    'data' => [
                        'orders' => $orders
                    ]
                ],
                200
            );
        }

        return response()->json(
            [
                'status' => true,
                'code' => 'SUCCESS',
                'data' => 'Orders Not Exist Add One !'
            ],
            200
        );
    }


    /**
     * Display Confirmed Orders
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function confirmedOrders(Request $request)
    {
        if (!$request->user()->can('order_show')) {
            return response()->json(
                [
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To See Orders',
                ],
                405
            );
        }

        $confirmedOrders = Order::where([['agente_id', $request->user()->id], ['confirmation', 'confirmer']])->get();
        if (count($confirmedOrders) > 0) {
            return response()->json(
                [
                    'status' => true,
                    'code' => 'SUCCESS',
                    'data' => [
                        'orders' => $confirmedOrders
                    ]
                ],
                200
            );
        }

        return response()->json(
            [
                'status' => true,
                'code' => 'SUCCESS',
                'data' => 'No Order Confirmed Yet !'
            ],
            200
        );
    }

    /**
     * Display Confirmed Orders
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addOrder(Request $request)
    {
        try {
            if (!$request->user()->can('order_update')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Update Order Orders',
                    ],
                    405
                );
            }
            
           
            $countOrderNotConfirmed = Order::where([['agente_id',$request->user()->id],['confirmation',null]])->count();

            if( $countOrderNotConfirmed>0){
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'ORDER_NOT_CONFIRMED',
                        'message' => 'An Order Not Confirmed'
                    ],
                    200
                );
            }

            $product_id = ProductAgente::where('agente_id',$request->user()->id)->value('product_id');
            $product_name = Product::find($product_id)->value('name');
            
            $AddOrder = Order::where([['agente_id',null],['product_name',$product_name]])->first();

            if ($AddOrder) {
                $AddOrder->agente_id = $request->user()->id;
                $AddOrder->save();
            }else{
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'message' => 'Orders Doesnt not Exist Now !'
                    ],
                    200
                );
            }

            return response()->json(
                [
                    'status' => true,
                    'code' => 'SUCCESS',
                    'data' => [
                        'orders' => $AddOrder
                    ]
                ],
                200
            );

        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'code' => 'SERVER_ERROR',
                    'message' => $th->getMessage(),
                ],
                500
            );
        }
    }
}
