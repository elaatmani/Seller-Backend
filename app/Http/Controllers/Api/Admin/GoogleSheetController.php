<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\SheetHelper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Sheet;
use Revolution\Google\Sheets\Facades\Sheets;

class GoogleSheetController extends Controller
{
    public function index() {

        try {
            $sheets = Sheet::where('auto_fetch', 1)->get();
            $sheet_helper = new SheetHelper();

            $newOrders = [];

            foreach($sheets as $sheet) {
                try {
                    $orders = $sheet_helper->sync_orders($sheet, false);
                    $newOrders = [...$newOrders, ...$orders];
                    $sheet->active = true;
                    $sheet->save();
                } catch (\Throwable $th) {
                    if($th->getMessage() == 'PERMISSION_DENIED') {
                        $sheet->active = false;
                        $sheet->save();
                    }
                    continue;
                }
            }

            return response()->json([
                'status' => true,
                'code' => 'SUCCESS',
                'data' => [
                    'orders' => $newOrders
                ]
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 'SERVER_ERROR',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function sync_orders(Request $request, $id) {
        try {

            $sheet = Sheet::find($id);

            if (!isset($sheet)) {

                return response()->json([
                    'status' => false,
                    'code' => 'NOT_FOUND',
                ]);
            }

            $sheet_helper = new SheetHelper();
            $orders = $sheet_helper->sync_orders($sheet, false);
            $sheet->active = true;
            $sheet->save();

            return response()->json(
                [
                    'status' => true,
                    'code' => 'SUCCESS',
                    'data' => [
                        'orders' => $orders,
                        'sheet' => $sheet
                    ],
                ],
                200
            );
        } catch (\Throwable $th) {
            if($th->getMessage() == 'PERMISSION_DENIED') {

                $sheet = Sheet::find($id);
                $sheet->active = false;
                $sheet->save();

                return response()->json(
                    [
                        'status' => false,
                        'message' => $th->getMessage(),
                        'code' => 'PERMISSION_DENIED',
                        'data' => [
                            'sheet' => $sheet
                        ]
                    ],
                    500
                );
            }
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


    public function save_orders(Request $request, $id) {
        try {

            $sheet = Sheet::find($id);

            if (!isset($sheet)) {

                return response()->json([
                    'status' => false,
                    'code' => 'NOT_FOUND',
                ]);
            }

            $sheet_helper = new SheetHelper();
            $orders = $sheet_helper->sync_orders($sheet);

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



    public function test() {
        $sheet_id = "1RBisw6xN9ifUYHoQImqKE5B0h2j6JDlDUkyeAiCJSmE";
        $sheet_name = "Sheet1";
        $sheet = new Sheet();
        $sheet->sheet_id = $sheet_id;
        $sheet->sheet_name = $sheet_name;

        $sheet_helper = new SheetHelper();

        return $sheet_helper->sync_orders($sheet, false);
    }


}
