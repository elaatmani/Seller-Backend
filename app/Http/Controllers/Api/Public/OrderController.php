<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Factorisation;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductAgente;
use App\Models\ProductVariation;
use App\Models\User;
use App\Services\RoadRunnerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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



        $orders = Order::with(['items' => ['product', 'product_variation.warehouse']])->where([['agente_id', $request->user()->id], ['confirmation', '!=', 'confirmer']])->get();

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
                    'orders' => []
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



        $order = Order::with(['items' => ['product', 'product_variation']])->where([['agente_id', $request->user()->id], ['confirmation', null]])->get();

        if (count($order) > 0) {
            $checkOrder = Order::where('fullname', $order[0]->fullname)
                    ->where('phone', $order[0]->phone)
                    ->where('city', $order[0]->city)
                    ->where('product_name', $order[0]->product_name)
                    ->where('agente_id', $order[0]->agente_id)
                    ->where('confirmation', null)
                    ->get();

            return response()->json(
                [
                    'status' => true,
                    'code' => 'SUCCESS',
                    'data' => [
                        'orders' => $order,
                        'double' => $checkOrder->count() > 1,
                        'double_orders' => $checkOrder
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
     * Update a sale and its associated order items.
     *
     * @param \Illuminate\Http\Request  $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function updateOrder(Request $request, $id)
    {
        try {
            if (!$request->user()->can('update_sale')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Update Sales',
                    ],
                    405
                );
            }
             $roadrunner = [
                // 'ip_address' => $_SERVER['SERVER_ADDR'],
                // 'domain' => $_SERVER['HTTP_HOST'],
                'request' => "NONE",
                'response' => NULL,
                ];
            DB::beginTransaction();


            $sale = Order::where('id', $id)->first();

            if ($sale) {
                $sale->fullname = $request->fullname;
                $sale->phone = $request->phone;
                $sale->city = $request->city;
                $sale->city_id = $request->city_id;
                $sale->adresse = $request->adresse;
                $sale->price = $request->price;
                $sale->note = $request->note;
                $sale->counts_from_warehouse = $request->counts_from_warehouse;

                if ($request->upsell != $sale->upsell) {
                    $sale->upsell = $request->upsell;

                    $orderHistory = new OrderHistory();
                    $orderHistory->order_id = $sale->id;
                    $orderHistory->user_id = $request->user()->id;
                    $orderHistory->historique = !!$request->upsell ? $request->upsell : 'Select';
                    $orderHistory->type = 'upsell';
                    $orderHistory->note = 'Updated Status of Upsell';
                    $orderHistory->save();
                }

                if ($sale->confirmation == 'reporter') {
                    $sale->reported_agente_date = $request->reported_agente_date;
                    $sale->reported_agente_note = $request->reported_agente_note;
                }

                if ($request->confirmation != $sale->confirmation) {
                    $sale->confirmation = $request->confirmation;

                    if ($sale->confirmation === 'reporter') {
                        $sale->reported_agente_date = $request->reported_agente_date;
                        $sale->reported_agente_note = $request->reported_agente_note;
                    }


                    $orderHistory = new OrderHistory();
                    $orderHistory->order_id = $sale->id;
                    $orderHistory->user_id = $request->user()->id;
                    $orderHistory->historique = !!$request->confirmation ? $request->confirmation : 'Select';
                    $orderHistory->type = 'confirmation';
                    $orderHistory->note = 'Updated Status of Confirmation';
                    $orderHistory->save();
                }




                $sale->save();

                // Update or delete order items
                $orderItems = $request->orderItems;
                $existingOrderItemIds = [];

                $existingItems = collect($request->orderItems)->groupBy(function ($item) {
                    return  $item['product_id'] . '_' . $item['product_ref'] . '_' . $item['product_variation_id'];
                })->map(function ($groupedItems) {
                    $sumQuantity = collect($groupedItems)->sum('quantity');
                    $sumPrice = collect($groupedItems)->sum('price');
                    $firstItem = $groupedItems[0];
                    $firstItem['quantity'] = $sumQuantity;
                    $firstItem['price'] = $sumPrice;
                    return $firstItem;
                })->values()->toArray();

                foreach ($existingItems as $orderItem) {
                    $orderItem = OrderItem::updateOrCreate(['id' => $orderItem['id'], 'product_variation_id' => $orderItem['product_variation_id']], [
                        'order_id' => $sale->id,
                        'product_id' => $orderItem['product_id'],
                        'product_ref' => $orderItem['product_ref'],
                        'product_variation_id' => $orderItem['product_variation_id'],
                        'quantity' => $orderItem['quantity'],
                        'price' => $orderItem['price']
                    ]);

                    $existingOrderItemIds[] = $orderItem->id;
                }



                // Delete order items that are not in the request
                $sale->items()->whereNotIn('id', $existingOrderItemIds)->delete();
                $sale = Order::with(['items' => ['product_variation.warehouse', 'product']])->where('id', $sale->id)->first();


                if ($request->affectation != $sale->affectation) {
                    if($sale->affectation == 4 && $request->affectation != 4){
                           $roadrunner = [
                               // 'ip_address' => $_SERVER['SERVER_ADDR'],
                               // 'domain' => $_SERVER['HTTP_HOST'],
                               'request' => 'DELETE',
                               'response' => RoadRunnerService::delete($sale->id),
                           ];

                           if(($roadrunner['response'] == false) || (is_array($roadrunner['response']) && array_key_exists('error', $roadrunner['response']))) {
                               return response()->json(
                                   [
                                       'status' => false,
                                       'code' => 'ERROR',
                                       'response' => $roadrunner,
                                       'message' => "Road Runner: " . ($roadrunner['response'] == false ? 'Something went wrong' : $roadrunner['response']['error']),
                                   ],
                                   500
                               );
                           }
                       }

                       $sale->affectation = $request->affectation;


                    if ($request->affectation != null) {
                            $sale->delivery = 'dispatch';
                           if($request->affectation == 4 && $sale->confirmation == 'confirmer'){
                               $roadrunner = [
                                   // 'ip_address' => $_SERVER['SERVER_ADDR'],
                                   // 'domain' => $_SERVER['HTTP_HOST'],
                                   'request' => 'INSERT',
                                   'response' => RoadRunnerService::insert($sale),
                               ];

                               if(($roadrunner['response'] == false) || (is_array($roadrunner['response']) && array_key_exists('error', $roadrunner['response']))) {

                                    if(data_get($roadrunner, 'response.error') != "Can not add order, you may change reference ID") {
                                        $sale->affectation = NULL;
                                        $sale->delivery = NULL;
                                        $sale->save();
                                        return response()->json(
                                            [
                                                'status' => false,
                                                'code' => 'ERROR',
                                                'response' => $roadrunner,
                                                'message' => "Road Runner: " . ($roadrunner['response'] == false ? 'The city is not valid.' : $roadrunner['response']['error']),
                                            ],
                                            500
                                        );
                                    }

                                    $sale->affectation = $request->affectation;
                                    $sale->save();
                               }
                           }
                   } else {
                    $sale->delivery = null;
                   }


                   $deliveryUser = User::find($request->affectation);
                    $delivery = !!$request->affectation ? $deliveryUser->firstname . ' ' . $deliveryUser->lastname : 'Select';
                   $orderHistory = new OrderHistory();
                   $orderHistory->order_id = $sale->id;
                   $orderHistory->user_id = $request->user()->id;
                   $orderHistory->historique = $delivery;
                   $orderHistory->type = 'affectation';
                   $orderHistory->note = 'Updated Status of Affectation';
                   $orderHistory->save();
               }

               $sale->save();
               DB::commit();

                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'message' => 'Sale and Order Items Updated Successfully!',
                        'data' => [
                            'sale' => $sale
                        ]
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_FOUND',
                        'message' => 'Sale not found',
                    ],
                    404
                );
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(
                [
                    'status' => false,
                    'message' => $th->getMessage(),
                    'code' => 'SERVER_ERROR',
                ],
                500
            );
        }
    }


    /**
     * Update an order.
     *
     * @param \Illuminate\Http\Request  $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    // public function updateOrder(Request $request, $id)
    // {
    //     try {
    //         if (!$request->user()->can('update_order')) {
    //             return response()->json(
    //                 [
    //                     'status' => false,
    //                     'code' => 'NOT_ALLOWED',
    //                     'message' => 'You Dont Have Access To Update Orders',
    //                 ],
    //                 405
    //             );
    //         }

    //         $order = Order::where('id', $id)->first();

    //         if ($order) {
    //             $order->fullname = $request->fullname;
    //             $order->product_name = $request->product_name;
    //             $order->upsell = $request->upsell;
    //             $order->phone = $request->phone;
    //             $order->city = $request->city;
    //             $order->adresse = $request->adresse;
    //             $order->quantity = $request->quantity;
    //             $order->confirmation = $request->confirmation;
    //             $order->affectation = $request->affectation;
    //             if ($request->delivery) {
    //                 $order->delivery = $request->delivery;
    //             }
    //             $order->price = $request->price;
    //             if ($request->note) {
    //                 $order->note = $request->note;
    //             }

    //             $order->save();

    //             return response()->json(
    //                 [
    //                     'status' => true,
    //                     'code' => 'SUCCESS',
    //                     'data' => 'Order Updated Successfully!'
    //                 ],
    //                 200
    //             );
    //         } else {
    //             return response()->json(
    //                 [
    //                     'status' => false,
    //                     'code' => 'NOT_FOUND',
    //                     'message' => 'Order not found',
    //                 ],
    //                 404
    //             );
    //         }
    //     } catch (\Throwable $th) {
    //         return response()->json(
    //             [
    //                 'status' => false,
    //                 'message' => $th->getMessage(),
    //                 'code' => 'SERVER_ERROR'
    //             ],
    //             500
    //         );
    //     }
    // }



    /**
     * Update an order.
     *
     * @param \Illuminate\Http\Request  $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function updateConfirmationAndNote(Request $request, $id)
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
                DB::beginTransaction();
                $order->confirmation = $request->confirmation;
                $order->note = $request->note;
                if ($request->confirmation === 'reporter') {
                    $order->reported_agente_date = $request->reported_agente_date;
                    $order->reported_agente_note = $request->reported_agente_note;
                }


                $order->save();


                $orderHistory = new OrderHistory();
                $orderHistory->order_id = $id;
                $orderHistory->user_id = $request->user()->id;
                $orderHistory->type = 'confirmation';
                $orderHistory->historique = $request->confirmation;
                $orderHistory->note = 'Updated Status of Confirmation';
                $orderHistory->save();
                DB::commit();


                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => 'Confirmation And Note Updated Successfully!'
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
                DB::beginTransaction();
                $order->confirmation = $request->confirmation;
                if ($request->confirmation === 'reporter') {
                    $order->reported_agente_date = $request->reported_agente_date;
                    $order->reported_agente_note = $request->reported_agente_note;
                }


                $order->save();

                $orderHistory = new OrderHistory();
                $orderHistory->order_id = $id;
                $orderHistory->user_id = $request->user()->id;
                $orderHistory->type = 'confirmation';
                $orderHistory->historique = $request->confirmation;
                $orderHistory->note = 'Updated Status of Confirmation';
                $orderHistory->save();
                DB::commit();


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
                // DB::beginTransaction();
                $order->note = $request->note;
                $order->save();



                // $orderHistory = new OrderHistory();
                // $orderHistory->order_id = $id;
                // $orderHistory->user_id = $request->user()->id;
                // $orderHistory->historique = $request->note;
                // $orderHistory->note = 'Updated The Agente Note';
                // $orderHistory->save();
                DB::commit();



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
     * Update order's Delivery Note .
     *
     * @param \Illuminate\Http\Request  $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function updateDeliveryNote(Request $request, $id)
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
                // DB::beginTransaction();
                $order->note = $request->note_d;
                $order->save();


                // $orderHistory = new OrderHistory();
                // $orderHistory->order_id = $id;
                // $orderHistory->user_id = $request->user()->id;
                // $orderHistory->historique = $request->note_d;
                // $orderHistory->note = 'Updated Status of Affectation';
                // $orderHistory->save();
                DB::commit();
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
                // return RoadRunnerService::getPrice($order);
                DB::beginTransaction();
                $order->delivery = $request->delivery;

                if ($order->confirmation === 'confirmer' && $request->delivery === 'livrer') {
                    $order->cmd = 'CMD-' . date('dmY-His', strtotime($order->created_at));
                    $order->delivery_date = now();
                    $existingFactorization = Factorisation::where('delivery_id', $order->affectation)
                        ->where('close', false)
                        ->first();

                    if ($existingFactorization) {
                        // Update the existing factorization
                        $existingFactorization->price += RoadRunnerService::getPrice($order);
                        $existingFactorization->commands_number += 1;
                        $existingFactorization->save();

                        $order->factorisation_id = $existingFactorization->id;
                    } else {
                        // Create a new factorization
                        $newFactorization = Factorisation::create([
                            'factorisation_id' => 'FCT-' . date('dmY-His', strtotime($order->delivery_date)),
                            'delivery_id' => $order->affectation,
                            'commands_number' => +1,
                            'price' => RoadRunnerService::getPrice($order),
                        ]);

                        $order->factorisation_id = $newFactorization->id;
                    }
                }

                if ($order->factorisation_id) {
                    if ($request->delivery !== 'livrer') {
                        $order->delivery_date = null;

                        $oldFactorisation = Factorisation::find($order->factorisation_id);
                        if ($oldFactorisation) {
                            $oldFactorisation->price -= RoadRunnerService::getPrice($order);
                            $oldFactorisation->commands_number -= 1;
                            $oldFactorisation->save();
                            if ($oldFactorisation->commands_number == 0) {
                                $oldFactorisation->delete();
                            }
                        }
                        $order->factorisation_id = null;
                    }
                }

                if ($request->delivery === 'expidier') {
                    $orderItems = OrderItem::where('order_id', $request->id)->get();
                    foreach ($orderItems as $orderItem) {
                        $products = ProductVariation::where('id', $orderItem->product_variation_id)->get();

                        if ($orderItem->quantity > $products->value('quantity')) {
                            return response()->json(
                                [
                                    'status' => false,
                                    'code' => 'QUANTITY_ERROR',
                                    'message' => "Quantity of variation '" . $products->value('size') . " / " . $products->value('color') . "' should be greater than " . $products->value('quantity')
                                ],
                                200
                            );
                        }
                    }
                }
                if ($request->delivery === 'reporter') {
                    $order->reported_delivery_date = $request->reported_delivery_date;
                    $order->reported_delivery_note = $request->reported_delivery_note;
                }
                $order->save();


                $orderHistory = new OrderHistory();
                $orderHistory->order_id = $id;
                $orderHistory->user_id = $request->user()->id;
                $orderHistory->type = 'delivery';
                $orderHistory->historique = $request->delivery;
                $orderHistory->note = 'Updated Status of Delivery';
                $orderHistory->save();
                DB::commit();
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

            $order = Order::with('items.product_variation')->where('id', $id)->first();
            $roadrunner = [
                // 'ip_address' => $_SERVER['SERVER_ADDR'],
                // 'domain' => $_SERVER['HTTP_HOST'],
                'request' => "NONE",
                'response' => NULL,
                ];

            if ($order) {
                DB::beginTransaction();
                if($order->affectation == 4 && $request->affectation != 4){
                    $roadrunner = [
                        // 'ip_address' => $_SERVER['SERVER_ADDR'],
                        // 'domain' => $_SERVER['HTTP_HOST'],
                        'request' => 'DELETE',
                        'response' => RoadRunnerService::delete($order->id),
                    ];

                    if(($roadrunner['response'] == false) || (is_array($roadrunner['response']) && array_key_exists('error', $roadrunner['response']))) {

                        return response()->json(
                            [
                                'status' => false,
                                'code' => 'ERROR',
                                'response' => $roadrunner,
                                'message' => "Road Runner: " . ($roadrunner['response'] == false ? 'Something went wrong' : $roadrunner['response']['error']),
                            ],
                            500
                        );
                    }

                    $order->delivery = null;
                    $order->save();
                }
                $order->affectation = $request->affectation;
                $orderHistory = new OrderHistory();
                $orderHistory->order_id = $id;
                $orderHistory->user_id = $request->user()->id;
                $orderHistory->type = 'affectation';
                if ($request->affectation != null) {
                    $order->delivery = 'dispatch';
                    if($request->affectation == 4){
                        $roadrunner = [
                            // 'ip_address' => $_SERVER['SERVER_ADDR'],
                            // 'domain' => $_SERVER['HTTP_HOST'],
                            'request' => 'INSERT',
                            'response' => RoadRunnerService::insert($order),
                        ];

                        if(($roadrunner['response'] == false) || (is_array($roadrunner['response']) && array_key_exists('error', $roadrunner['response']))) {

                            if(data_get($roadrunner, 'response.error') != "Can not add order, you may change reference ID") {
                                $order->affectation = NULL;
                                $order->delivery = NULL;
                                $order->save();
                                return response()->json(
                                    [
                                        'status' => false,
                                        'code' => 'ERROR',
                                        'response' => $roadrunner,
                                        'message' => "Road Runner: " . ($roadrunner['response'] == false ? 'City is not valid.' : $roadrunner['response']['error']),
                                    ],
                                    500
                                );
                            }

                            $order->affectation = $request->affectation;
                            $order->save();
                       }
                    }

                    $deliveryUser = User::find($request->affectation);
                    $delivery = $deliveryUser->firstname . ' ' . $deliveryUser->lastname;
                    $orderHistory->historique = $delivery;
                } else {
                    $order->delivery = null;
                }
                $order->save();
                $orderHistory->note = 'Updated Status of Affectation';
                $orderHistory->save();
                DB::commit();
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'roadrunner' => $roadrunner,
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
                DB::beginTransaction();
                $order->upsell = $request->upsell;
                $order->save();


                $orderHistory = new OrderHistory();
                $orderHistory->order_id = $id;
                $orderHistory->user_id = $request->user()->id;
                $orderHistory->historique = $request->upsell;
                $orderHistory->type = 'upsell';
                $orderHistory->note = 'Updated Status of Upsell';
                $orderHistory->save();
                DB::commit();
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

        $confirmedOrders = Order::with(['items' => ['product', 'product_variation.warehouse']])->where([['agente_id', $request->user()->id], ['confirmation', 'confirmer']])->get();
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


            $countOrderNotConfirmed = Order::where([['agente_id', $request->user()->id], ['confirmation', null]])->get();

            if ($countOrderNotConfirmed->count() > 0) {
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'ORDER_NOT_CONFIRMED',
                        'message' => 'An Order Not Confirmed',
                        'orders' => $countOrderNotConfirmed
                    ],
                    200
                );
            }



            //Bring product_ids handled by current agente
            $product_ids = ProductAgente::where('agente_id', $request->user()->id)->pluck('product_id');

            //Get the Order_ids related to the Product_ids handled by current agente
            $OrderItems = OrderItem::whereIn('product_id', $product_ids)->pluck('order_id');

            //Check and get the order_ids if they have both agente and confirmation null
            $AddOrder = Order::with(['items' => ['product', 'product_variation']])
                ->whereIn('id', $OrderItems)
                ->whereNull('agente_id')
                ->whereNull('confirmation')
                ->first();



            if ($AddOrder) {
                $checkOrder = Order::where('fullname', $AddOrder->fullname)
                    ->where('phone', $AddOrder->phone)
                    ->where('city', $AddOrder->city)
                    ->where('product_name', $AddOrder->product_name)
                    ->whereNull('agente_id')
                    ->get();

                if ($checkOrder->count() > 1) {
                    DB::beginTransaction();
                    // $firstOrder = $checkOrder->first();

                    foreach ($checkOrder as $order) {
                        $order->agente_id = $request->user()->id;
                        $order->dropped_at = now();
                        // $order->double = $firstOrder->id;
                        $order->save();

                        $orderHistory = new OrderHistory();
                        $orderHistory->order_id = $order->id;
                        $orderHistory->user_id = $request->user()->id;
                        $orderHistory->type = 'responsibility';
                        $orderHistory->note = 'Got the Order';
                        $orderHistory->save();
                    }

                    DB::commit();
                } else {
                    // Only one order or no duplicates found
                    // Continue with the original code without any modifications
                    DB::beginTransaction();
                    $AddOrder->agente_id = $request->user()->id;
                    $AddOrder->dropped_at = now();
                    $AddOrder->save();

                    $orderHistory = new OrderHistory();
                    $orderHistory->order_id = $AddOrder->id;
                    $orderHistory->user_id = $request->user()->id;
                    $orderHistory->type = 'responsibility';
                    $orderHistory->note = 'Got the Order';
                    $orderHistory->save();
                    DB::commit();
                }
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
                        'orders' => $AddOrder,
                        'double' => $checkOrder->count() > 1,
                        'double_orders' => $checkOrder
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


    /**
     * Display delivered orders.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function orderHistory(Request $request, $id)
    {
        try {
            if (!$request->user()->can('view_order')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Delivery Orders',
                    ],
                    405
                );
            }
            $orderHistory = OrderHistory::where('order_id', $id)->with('orders', 'users')->get();

            if (count($orderHistory) > 0) {
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => [
                            'orders' => $orderHistory,
                        ]
                    ],
                    200
                );
            }

            return response()->json(
                [
                    'status' => true,
                    'code' => 'NO_ORDER',
                    'message' => 'ORDER_NOT_FOUND',
                    'data' => [
                        'orders' => [],
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
    public function orderScanner(Request $request, $id)
    {
        try {
            if (!$request->user()->can('handle_expidation')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Delivery Orders',
                    ],
                    405
                );
            }

            $order = Order::find($id);

            if (count($order) > 0) {

                $order->delivery = $request->delivery;
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'message' => 'Order Delivery Status Updated Successfuly',
                        'data' => [
                            'orders' => $order,
                        ]
                    ],
                    200
                );
            }

            return response()->json(
                [
                    'status' => true,
                    'code' => 'NO_ORDER',
                    'message' => 'ORDER_NOT_FOUND',
                    'data' => [
                        'orders' => [],
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
    public function orderCount(Request $request)
    {

        try {
            if (!$request->user()->can('show_all_orders')) {

                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'message' => 'Availble Order Count',
                        'data' => [
                            'availble' => 0,
                        ]
                    ],
                    200
                );
                return response()->json(
                    [
                        'status' => false,
                        'code' => '',
                        'message' => 'You Dont Have Access To Delivery Orders',
                    ],
                    405
                );
            }

            //Bring product_ids handled by current agente
            $product_ids = ProductAgente::where('agente_id', $request->user()->id)->pluck('product_id');

            //Get the Order_ids related to the Product_ids handled by current agente
            $OrderItems = OrderItem::whereIn('product_id', $product_ids)->pluck('order_id');

            //Check and get the order_ids if they have both agente and confirmation null
            $AddOrder = Order::with(['items' => ['product', 'product_variation']])->whereIn('id', $OrderItems)->whereNull('agente_id')
                ->whereNull('confirmation')
                ->count();


            return response()->json(
                [
                    'status' => true,
                    'code' => 'SUCCESS',
                    'message' => 'Availble Order Count',
                    'data' => [
                        'availble' => $AddOrder,
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
