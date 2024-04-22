<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;

class OrderWarehouseController extends Controller
{
    public function __invoke(Request $request)
    {
        $id = $request->input('id');
        $target = $request->input('target');

        $order = Order::where('id', $id)->first();

        if(!$order) {
            return response()->json([
                'code' => 'NOT_FOUND'
            ], 404);
        }

        if($target == 'in') {
            $order->delivery = 'in-warehouse';
        }
        $targetParsed = $target == 'in';
        $order->in_warehouse = $targetParsed;
        $order->scanned_at = now();

        $order->save();

        return response()->json([
            'message' => 'Invoked.',
            'code' => 'SUCCESS',
            'order' => $order,
            'target' => $target
        ]);
    }
}
