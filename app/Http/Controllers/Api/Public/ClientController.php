<?php

namespace App\Http\Controllers\Api\Public;

use App\Models\Order;
use App\Models\OrderHistory;
use Illuminate\Http\Request;
use App\Models\RoadRunnerRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{

    public function updateDelivery(Request $request) {
        try {
            DB::beginTransaction();


            $id = substr($request->reference_id, 6);
            $order = Order::where('id', $id)->first();

            $roadrunner = RoadRunnerRequest::create([
                'reference_id' => $request->reference_id,
                'status' => $request->status
            ]);

            if(!$order) {
                $roadrunner->success = false;
                $roadrunner->message = "Order not found";
                $roadrunner->save();

                return response()->json([
                    'code' => 'NOT_FOUND',
                    'message' => 'Order was not found'
                ], 404);
            }

            if($order->affectation != 4) {

            }

            $statuses = [
                'New',
                'Picked up',
                'Transfer',
                'Delayed',
                'Delivered',
                'Cancelled',
                'Returned',
                'Delivered & return',
                'Paid'
            ];

            $references = [
                'New' => 'dispatch',
                'Picked up' => 'expidier',
                'Transfer' => 'transfer',
                'Delayed' => 'pas-de-reponse',
                'Delivered' => 'livrer',
                'Cancelled' => 'annuler',
                'Returned' => 'retourner',
                'Delivered & return' => 'refuser',
                'Paid' => 'paid'
            ];

            $roadrunner->success = true;
            $roadrunner->message = "Order delivery status has changed to '" . $request->status . "'.";

            if(!in_array($request->status, $statuses)) {
                $newStatus = $order->delivery;
                $roadrunner->message = "The state '" . $request->status ."' was not found. order delivery stays in '" . $order->delivery . "'.";
            } else {
                $newStatus = $references[$request->status];

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
}
