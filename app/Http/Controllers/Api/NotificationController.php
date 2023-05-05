<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{

     /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function adminNotification(){
        try {
            $stockAlert = Product::whereHas('variations', function ($query) {
                $query->where('quantity', '<=', DB::raw('stockAlert'));
            })->with('variations')->get();
            
            $reportedSale = Order::where(function ($query) {
                $query->whereNotNull('confirmation_reported')
                      ->orWhereNotNull('delivery_reported');
            })->get();
 
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => [
                            'productStockAlert' => $stockAlert,
                            'reportedSale' => $reportedSale
                        ],
                        'message' => 'You don\'n have enough Stock'
                    ],
                    200
                );
           
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
    
}
