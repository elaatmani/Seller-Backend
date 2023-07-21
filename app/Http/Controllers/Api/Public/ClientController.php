<?php

namespace App\Http\Controllers\Api\Public;

use App\Models\Order;
use App\Models\OrderHistory;
use Illuminate\Http\Request;
use App\Models\RoadRunnerRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    
    
    public $statuses = [
        'New',
        'Picked Up',
        'Transfer',
        'Delayed',
        'Delivered',
        'Canceled',
        'Returned',
        'Delivered & Return',
        'Paid'
    ];

    public $references = [
        'New' => 'dispatch',
        'Picked Up' => 'expidier',
        'Transfer' => 'transfer',
        'Delayed' => 'pas-de-reponse',
        'Delivered' => 'livrer',
        'Canceled' => 'annuler',
        'Returned' => 'retourner',
        'Delivered & Return' => 'livrer',
        'Paid' => 'paid'
    ];

    public function updateDelivery(Request $request)
    {
        try {
            DB::beginTransaction();


            $id = substr($request->reference_id, 11);
            $order = Order::where('id', $id)->first();

            $roadrunner = RoadRunnerRequest::create([
                'reference_id' => $request->reference_id,
                'status' => $request->status
            ]);

            if (!$order) {
                $roadrunner->success = false;
                $roadrunner->message = "Order not found";
                $roadrunner->save();

                return response()->json([
                    'code' => 'NOT_FOUND',
                    'message' => 'Order was not found'
                ], 404);
            }

            if ($order->affectation != 4) {
            }


            $roadrunner->success = true;
            $roadrunner->message = "Order delivery status has changed to '" . $request->status . "'.";

            if (!in_array($request->status, $this->statuses)) {
                $newStatus = $order->delivery;
                $roadrunner->message = "The state '" . $request->status . "' was not found. order delivery stays in '" . $order->delivery . "'.";
            } else {
                $newStatus = $this->references[$request->status];

                $orderHistory = new OrderHistory();
                $orderHistory->order_id = $order->id;
                $orderHistory->user_id = auth()->user()->id;
                $orderHistory->type = 'delivery';
                $orderHistory->historique = $newStatus;
                $orderHistory->note = 'Updated Status of Delivery';
                $orderHistory->save();
            }

            $roadrunner->save();

            $order->delivery = $newStatus;
            $order->save();



            DB::commit();


            return response()->json([
                'code' => 'SUCCESS',
                'message' => "Order delivery status has changed to '" . $request->status . "'."
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            $roadrunner = RoadRunnerRequest::create([
                'reference_id' => $request->reference_id,
                'status' => $request->status,
                'success' => false,
                'message' => $th->getMessage()
            ]);

            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => $th->getMessage()
            ], 500);
        }
    }


    public function updateMultipleDelivery(Request $request)
    {
        try {
            DB::beginTransaction();

            $references = $request->orders;

            $validate = Validator::make($request->all(), ['orders' => 'required']);

            if($validate->fails()) {
                return response()->json([
                    'code' => 'VALIDATION_ERROR',
                    'errors' => $validate->errors()
                ]);
            }

            $success = [];
            $failed = [];

            foreach ($references as $res) {

                try {
                    $id = substr($res['reference_id'], 11);
                    $order = Order::where('id', $id)->first();

                    $roadrunner = RoadRunnerRequest::create([
                        'reference_id' => $res['reference_id'],
                        'status' => $res['status']
                    ]);

                    if (!$order) {
                        $roadrunner->success = false;
                        $roadrunner->message = "Order not found";
                        $roadrunner->save();

                        $failed[] = [
                            'reference_id' => $res['reference_id'],
                            'error' => 'Order not found'
                        ];
                        continue;
                    }


                    $roadrunner->success = true;
                    $roadrunner->message = "Order delivery status has changed to '" . $res['status'] . "'.";

                    if (!in_array($res['status'], $this->statuses)) {
                        $newStatus = $order->delivery;
                        $roadrunner->message = "The state '" . $res['status'] . "' was not found. order delivery stays in '" . $order->delivery . "'.";

                        $failed[] = [
                            'reference_id' => $res['reference_id'],
                            'error' => "'Status not found: '" . $res['status'] . "'"
                        ];
                        continue;

                    } else {
                        $newStatus = $this->references[$res['status']];

                        $orderHistory = new OrderHistory();
                        $orderHistory->order_id = $order->id;
                        $orderHistory->user_id = auth()->user()->id;
                        $orderHistory->type = 'delivery';
                        $orderHistory->historique = $newStatus;
                        $orderHistory->note = 'Updated Status of Delivery';
                        $orderHistory->save();
                    }

                    $roadrunner->save();

                    $order->delivery = $newStatus;
                    $order->save();

                    $success[] = $res['reference_id'];

                } catch (\Throwable $th) {
                    $failed[] = [
                        'reference_id' => $res['reference_id'],
                        'error' => $th->getMessage()
                    ];
                    continue;
                }
            }



            DB::commit();


            return response()->json([
                'code' => 'SUCCESS',
                'success' => $success,
                'failed' => $failed,
            ]);


        } catch (\Throwable $th) {
            DB::rollBack();

            // $roadrunner = RoadRunnerRequest::create([
            //     'reference_id' => $references,
            //     'status' => $statuses,
            //     'success' => false,
            //     'message' => $th->getMessage()
            // ]);

            return response()->json([
                'code' => 'SERVER_ERROR',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
