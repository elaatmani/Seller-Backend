<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class SaleController extends Controller
{
    /**
     * Display all Sales.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('show_all_sales')) {
            return response()->json(
                [
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To See Products',
                ],
                405
            );
        }
        $orders = Order::orderBy('id', 'DESC')->get();

        return response()->json(
            [
                'status' => true,
                'code' => 'SUCCESS',
                'data' => [
                    'orders' => $orders,
                ],
            ],
            200
        );
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {

            if (!$request->user()->can('create_sale')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Create Sale',
                    ],
                    405
                );
            }


            //Validated
            $saleValidator = Validator::make(
                $request->all(),
                [
                    'fullname' => 'required',
                    'product_name' => 'required',
                    'phone' => 'required',
                    'city' => 'required',
                    'adresse' => 'required',
                    'quantity' => 'required|integer',
                    'price' => 'required|integer'
                ]
            );

            if ($saleValidator->fails()) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'validation error',
                        'error' => $saleValidator->errors()
                    ],
                    401
                );
            }

            DB::beginTransaction();
             $sale = Order::create([
                'fullname' => $request->fullname,
                'product_name' => $request->product_name,
                'phone' => $request->phone,
                'city' => $request->city,
                'adresse' => $request->adresse,
                'quantity' => $request->quantity,
                'price' => $request->price
             ]);
            DB::commit();



            return response()->json([
                'status' => true,
                'code' => 'SALE_ADDED',
                'message' => 'Sale Added Successfully!',
                'data' => $sale,
                200
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


     /**
     * Reset orders.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function saleReset(Request $request)
    {
        try {
            if (!$request->user()->can('reset_sale')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Reset Orders',
                    ],
                    405
                );
            }

            $orderIds = $request->input('ids');
            $orders = Order::whereIn('id', $orderIds)->get();
            foreach ($orders as $order) {
                $order->agente_id = null;
                $order->upsell = null;
                $order->confirmation = null;
                $order->affectation = null;
                $order->note = null;
                $order->note_d = null;
                $order->delivery = null;
                $order->reported_agente_date = null;
                $order->reported_agente_note = null;
                $order->reported_delivery_date = null;
                $order->reported_delivery_note = null;
                $order->save();
            }

            return response()->json(
                [
                    'status' => true,
                    'code' => 'SUCCESS',
                    'message' => 'Orders Reset Successfully',
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
