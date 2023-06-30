<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RoadRunnerRequest;
use Illuminate\Http\Request;

class ClientController extends Controller
{

    public function updateDelivery(Request $request) {
        try {
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

            $statuses = [
                'picked-up' => '',
                'transfer' => '',
                'delivered' => '',
                'canceled' => '',
                'returned' => '',
                'delayed' => '',
                'paid' => ''
            ];

            $roadrunner->success = true;
            $roadrunner->message = "Order delivery status has changed to '" . $request->status . "'.";
            $roadrunner->save();

            return response()->json([
                'code' => 'SUCCESS',
                'message' => "Order delivery status has changed to '" . $request->status . "'."
            ]);
        } catch (\Throwable $th) {
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
