<?php

namespace App\Http\Controllers\Api\Public;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\RoadRunnerRequest;
use App\Http\Controllers\Controller;
use App\Models\Factorisation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{


    public $statuses = [
        'New',
        'Picked Up',
        'Transfer',
        'Delay',
        'Delayed',
        'Delivered',
        'Cancel',
        'Canceled',
        'Returned',
        'Delivered & Return',
        'Paid',
        'Printed',
        'printed',
    ];

    public $references = [
        'New' => 'dispatch',
        'Picked Up' => 'expidier',
        'Transfer' => 'transfer',
        'Delay' => 'pas-de-reponse',
        'Delayed' => 'pas-de-reponse',
        'Delivered' => 'livrer',
        'Cancel' => 'annuler',
        'Canceleled' => 'annuler',
        'Returned' => 'retourner',
        'Delivered & Return' => 'livrer-et-retourner',
        'Paid' => 'livrer',
        'Printed' => 'printed',
        'printed' => 'printed',
    ];

    public function updateDelivery(Request $request)
    {
        try {
            DB::beginTransaction();


            $idold = substr($request->reference_id, 3);
            $prefixold = strtolower(substr($request->reference_id, 0, 3));

            $id = substr($request->reference_id, 4);
            $prefix = strtolower(substr($request->reference_id, 0, 4));
            if($prefix == 'cods' && is_numeric($id)){
                $order = Order::where('id', (int) $id)->first();
            } else {
                $order = null;
            }

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
                if(in_array($request->status , ['Paid', 'paid'])) {
                    $newStatus = $order->delivery;

                    $isDelievered = $request->delivered;
                    $isCanceled = $request->canceled;

                    if($isDelievered){
                        $order->is_delivered = true;
                        $order->is_paid_by_delivery = true;
                    }
                    if($isCanceled){
                        $order->is_canceled = true;
                    }

                    $roadrunner->message = "Order paid. current status '" . $newStatus . "'. is delivered: " . ($isDelievered ? 'TRUE' : 'FALSE') .  ". is canceled: " .  ($isCanceled ? 'TRUE' : 'FALSE');
                } else {
                    $newStatus = $this->references[$request->status];
                }

            }

            $roadrunner->save();

            $order->delivery = $newStatus;

            
            Log::channel('tracking')->info('Order Id: #' . $order->id . '; Order New Status: ' . $order->delivery . '; Request Status: ' . $newStatus);
            $order->save();



            DB::commit();


            return response()->json([
                'code' => 'SUCCESS',
                'message' => "Order delivery status has changed to '" . $request->status . "'..."
            ]);
        } catch (\Throwable $th) {
            Log::channel('tracking')->info('Error Single updating: ' . $request->reference_id);
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
                    $idold = substr($res['reference_id'], 3);

                    // $id = $idBefore - 2000;
                    $prefixold = strtolower(substr($res['reference_id'], 0, 3));


                     $id = substr($res['reference_id'], 4);

                    // $id = $idBefore - 2000;
                    $prefix = strtolower(substr($res['reference_id'], 0, 4));

                   if($prefix == 'cods' && is_numeric($id)){
                                    $order = Order::where('id', (int) $id)->first();
                                } else {
                        $order = null;
                    }



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
                    $roadrunner->message = "Order delivery status has changed to '" . $res['status'] . "'...";

                    if (!in_array($res['status'], $this->statuses)) {
                        $newStatus = $order->delivery;
                        $roadrunner->message = "The state '" . $res['status'] . "' was not found. order delivery stays in '" . $order->delivery . "'.";

                        $failed[] = [
                            'reference_id' => $res['reference_id'],
                            'error' => "'Status not found: '" . $res['status'] . "'"
                        ];
                        continue;

                    } else {
                        if(in_array($res['status'] , ['Paid', 'paid'])) {
                            $newStatus = $order->delivery;
                            $isDelievered = data_get($res, 'delivered', null);
                            $isCanceled = data_get($res, 'canceled', null);

                            if($isDelievered){
                                $order->is_delivered = true;
                                $order->is_paid_by_delivery = true;
                            }
                            if($isCanceled){
                                $order->is_canceled = true;
                            }

                            $roadrunner->message = "Order paid. current status '" . $newStatus . "'. is delivered: " . ($isDelievered ? 'TRUE' : 'FALSE') .  ". is canceled: " .  ($isCanceled ? 'TRUE' : 'FALSE');
                        } else {
                            $newStatus = $this->references[$res['status']];
                        }

                    }

                    $roadrunner->save();

                    $order->delivery = $newStatus;


                    Log::channel('tracking')->info('Order Id: #' . $order->id . '; Order New Status: ' . $order->delivery . '; Request Status: ' . $newStatus);
                    $order->save();

                    $success[] = $res['reference_id'];

                } catch (\Throwable $th) {
                    Log::channel('tracking')->info('Error Multiple updating: ' . $res['reference_id']);
                    Log::channel('tracking')->info('Error: ' . $th->getMessage());

                    $failed[] = [
                        'reference_id' => $res['reference_id'],
                        'error' => $th->getMessage()
                    ];

                    $roadrunner = RoadRunnerRequest::create([
                        'reference_id' => $res['reference_id'],
                        'status' => $res['status'],
                        'success' => false,
                        'message' => $th->getMessage()
                    ]);
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
            Log::channel('tracking')->info('Error updating multiple ');
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
