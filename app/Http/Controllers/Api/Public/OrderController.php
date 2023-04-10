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
     * Display a listing of orders still in confirmation.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if (!$request->user()->can('show_all_orders')) {
            return response()->json(
                [
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To See Orders',
                ],
                405
            );
        }



        $orders = Order::where([['agente_id', $request->user()->id], ['confirmation', '!=', 'confirmer']])->get();

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
                'message' => 'Add New One !',
                'data' => [
                    'orders' => ''
                ]
            ],
            200
        );
    }



    /**
     * Display an order not confirmed yet.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function orderToConfirme(Request $request)
    {

        if (!$request->user()->can('show_all_orders')) {
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
                'code' => 'NO_ORDER',
                'data' => 'Add New One !'
            ],
            200
        );
    }





    /**
     * Update an order.
     *
     * @param \Illuminate\Http\Request  $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function updateOrder(Request $request, $id)
    {
        try {
            if (!$request->user()->can('update_order')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Update Orders',
                    ],
                    405
                );
            }

            $order = Order::where('id', $id)->first();

            if ($order) {
                $order->fullname = $request->fullname;
                $order->product_name = $request->product_name;
                $order->upsell = $request->upsell;
                $order->phone = $request->phone;
                $order->city = $request->city;
                $order->adresse = $request->adresse;
                $order->quantity = $request->quantity;
                $order->confirmation = $request->confirmation;
                $order->affectation = $request->affectation;
                if ($request->delivery) {
                    $order->delivery = $request->delivery;
                }
                $order->price = $request->price; 
                if ($request->note) {
                    $order->note = $request->note;
                }
                
                $order->save();

                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => 'Order Updated Successfully!'
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_FOUND',
                        'message' => 'Order not found',
                    ],
                    404
                );
            }
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $th->getMessage(),
                    'code' => 'SERVER_ERROR'
                ],
                500
            );
        }
    }


     /**
     * Show an order.
     *
     * @param \Illuminate\Http\Request  $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function showOrder(Request $request, $id)
    {
        try {
            if (!$request->user()->can('view_order')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Update Orders',
                    ],
                    405
                );
            }

            $order = Order::where('id', $id)->get();

            if ($order) {
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => $order
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_FOUND',
                        'message' => 'Order not found',
                    ],
                    404
                );
            }
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $th->getMessage(),
                    'code' => 'SERVER_ERROR'
                ],
                500
            );
        }
    }


    /**
     * Update order's Confirmation .
     *
     * @param \Illuminate\Http\Request  $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function updateConfirmation(Request $request, $id)
    {
        try {
            if (!$request->user()->can('update_order')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Update Orders',
                    ],
                    405
                );
            }

            $order = Order::where('id', $id)->first();

            if ($order) {
                $order->confirmation = $request->confirmation;
                $order->save();

                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => 'Order\'s Confirmation Updated Successfully!'
                    ],
                    200
                );
            }
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $th->getMessage(),
                    'code' => 'SERVER_ERROR'
                ],
                500
            );
        }
    }




    /**
     * Update order's Confirmation .
     *
     * @param \Illuminate\Http\Request  $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function updateNote(Request $request, $id)
    {
        try {
            if (!$request->user()->can('update_order')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Update Orders',
                    ],
                    405
                );
            }

            $order = Order::where('id', $id)->first();

            if ($order) {
                $order->note = $request->note;
                $order->save();

                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => 'Order\'s Note Updated Successfully!'
                    ],
                    200
                );
            }
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $th->getMessage(),
                    'code' => 'SERVER_ERROR'
                ],
                500
            );
        }
    }



    /**
     * Update order's Confirmation .
     *
     * @param \Illuminate\Http\Request  $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function updateDelivery(Request $request, $id)
    {
        try {
            if (!$request->user()->can('update_order')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Update Orders',
                    ],
                    405
                );
            }

            $order = Order::where('id', $id)->first();

            if ($order) {
                $order->delivery = $request->delivery;
                $order->save();

                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => 'Order\'s Delivery Updated Successfully!'
                    ],
                    200
                );
            }
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $th->getMessage(),
                    'code' => 'SERVER_ERROR'
                ],
                500
            );
        }
    }



    /**
     * Update order's Confirmation .
     *
     * @param \Illuminate\Http\Request  $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function updateAffectation(Request $request, $id)
    {
        try {
            if (!$request->user()->can('update_order')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Update Orders',
                    ],
                    405
                );
            }

            $order = Order::where('id', $id)->first();

            if ($order) {
                $order->affectation = $request->affectation;
                $order->save();

                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => 'Order\'s Affectation Updated Successfully!'
                    ],
                    200
                );
            }
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $th->getMessage(),
                    'code' => 'SERVER_ERROR'
                ],
                500
            );
        }
    }



    /**
     * Update order's Confirmation .
     *
     * @param \Illuminate\Http\Request  $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function updateUpsell(Request $request, $id)
    {
        try {
            if (!$request->user()->can('update_order')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Update Orders',
                    ],
                    405
                );
            }

            $order = Order::where('id', $id)->first();

            if ($order) {
                $order->upsell = $request->upsell;
                $order->save();

                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => 'Order\'s Upsell Updated Successfully!'
                    ],
                    200
                );
            }
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $th->getMessage(),
                    'code' => 'SERVER_ERROR'
                ],
                500
            );
        }
    }



    /**
     * Display Confirmed Orders
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function confirmedOrders(Request $request)
    {

        if (!$request->user()->can('show_all_orders')) {
            return response()->json(
                [
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To See Confirmed Orders',
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
            if (!$request->user()->can('update_order')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Add Order',
                    ],
                    405
                );
            }


            $countOrderNotConfirmed = Order::where([['agente_id', $request->user()->id], ['confirmation', null]])->count();

            if ($countOrderNotConfirmed > 0) {
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'ORDER_NOT_CONFIRMED',
                        'message' => 'An Order Not Confirmed'
                    ],
                    200
                );
            }
            
            $product_ids = ProductAgente::where('agente_id', $request->user()->id)->pluck('product_id');
            $product_names = Product::whereIn('id', $product_ids)->pluck('name');
            $AddOrder = Order::whereNull('agente_id')->whereIn('product_name', $product_names)->get()->first();

            if ($AddOrder) {
                $AddOrder->agente_id = $request->user()->id;
                $AddOrder->save();
            } else {
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'NO_ORDERS',
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



    /**
     * Display order not Dlivered yet.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function orderToDelivery(Request $request)
    {
        try {
            if (!$request->user()->can('show_all_deliveries')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Delivery Orders',
                    ],
                    405
                );
            }
            $order = Order::where([['affectation', $request->user()->id], ['confirmation', 'confirmer'], [function ($query) {
                $query->where('delivery', '!=', 'livrer')
                      ->orWhereNull('delivery');
            }]])->get();

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
                    'code' => 'NO_ORDER_TO_DELIVERY',
                    'data' => [
                        'orders' => ''
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



    /**
     * Display delivered orders.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function orderDelivered(Request $request)
    {
        try {
            if (!$request->user()->can('show_all_deliveries')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Delivery Orders',
                    ],
                    405
                );
            }
            $order = Order::where([['affectation', $request->user()->id], ['confirmation', 'confirmer'], ['delivery', 'livrer']])->get();

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
                    'code' => 'NO_ORDER',
                    'data' => [
                        'orders' => ''
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
