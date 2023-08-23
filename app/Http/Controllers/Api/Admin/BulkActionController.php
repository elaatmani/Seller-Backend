<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkActionController extends Controller
{
    public function index(Request $request) {
        try {

            if(!auth()->user()->hasRole('admin')) {
                return response()->json([
                    'code' => 'NOT_ALLOWED',
                    'message' => 'Not allowed to this resources'
                ], 401);
            }

            $value = $request->value;
            $action = $request->action;

            if($value == 'not-selected') {
                return response()->json([
                    'code' => 'ERROR',
                    'message' => 'Value is not valid'
                ], 500);
            }

            if($action == 1) {
                return response()->json([
                    'code' => 'ERROR',
                    'message' => 'Action is not valid'
                ], 500);
            }

            $not_updated = [];
            foreach($request->selected as $id) {
                try {
                    $order = Order::where('id', $id)->first();

                    switch ($action) {
                        case 2:
                            $order->update([
                                'delivery' => $value
                            ]);
                        break;
                    }

                } catch (\Throwable $th) {
                    $not_updated[] = [
                        'id' => $id,
                        'message' => $th->getMessage(),
                        'trace' => $th->getTrace()
                    ];
                }
            }

            return response()->json([
                'code' => 'SUCCESS',
                'data' => [
                    'not_updated' => $not_updated,
                ],
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'code' => 'ERROR',
                'message' => $th->getMessage(),
                'trace' => $th->getTrace()
            ], 500);
        }
    }
}
