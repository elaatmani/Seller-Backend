<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CancelReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CancelReasonController extends Controller
{
    public function index(Request $request) {
        $reasons = CancelReason::query()->get();

        return response()->json([
            'code' => 'SUCCESS',
            'reasons' => $reasons
        ]);
    }

    public function store(Request $request) {

        if(!$request->reason) {
            return response()->json([
                'code' => 'ERROR',
                'message' => 'Reason is required'
            ], 400);
        }

        $existingReason = CancelReason::query()->where('reason', $request->reason)->first();

        if($existingReason) {
            return response()->json([
                'code' => 'SUCCESS',
                'reason' => $existingReason
            ]);
        }

        $reason = CancelReason::query()->create([
            'reason' => $request->reason,
            'created_by' => auth()->id()
        ]);

        return response()->json([
            'code' => 'SUCCESS',
            'reason' => $reason
        ]);
    }

    public function analytics(Request $request) {

        $product_id = $request->product_id;

        if($product_id) {
            $results = DB::table('orders')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->select('orders.cancel_reason as reason', DB::raw('COUNT(DISTINCT orders.id) as orders'))
                ->where('orders.confirmation', 'annuler')
                ->whereNotNull('orders.cancel_reason')
                ->where('order_items.product_id', $product_id)
                ->groupBy('orders.cancel_reason')
                ->get();
        } else {
            $results = DB::select('SELECT cancel_reason as reason, COUNT(*) as orders FROM orders WHERE confirmation = "annuler" AND cancel_reason is not null GROUP BY cancel_reason');
        }



        return response()->json([
            'code' => 'SUCCESS',
            'data' => $results
        ]);
    }


    public function products(Request $request) {

        $results = DB::select('SELECT id, name FROM products');

        return response()->json([
            'code' => 'SUCCESS',
            'data' => $results
        ]);
    }
}
