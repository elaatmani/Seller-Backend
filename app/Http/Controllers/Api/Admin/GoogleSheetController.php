<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\SheetHelper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Sheet;
use Revolution\Google\Sheets\Facades\Sheets;

class GoogleSheetController extends Controller
{
    public function index(Request $request) {

        try {
            // if (!$request->user()->can('show_all_sheets')) {
            //     return response()->json(
            //         [
            //             'status' => false,
            //             'code' => 'NOT_ALLOWED',
            //             'message' => 'You Dont Have Access To See Sheets',
            //         ],
            //         405
            //     );
            // }

            Auth::loginUsingId(1);
            $sheets = Sheet::where('auto_fetch', 1)->get();
            $sheet_helper = new SheetHelper();

            $newOrders = [];
            $done = [];
            $errors = [];

            foreach($sheets as $sheet) {
                try {
                    $orders = $sheet_helper->sync_orders($sheet);
                    $done[] = ['id' => $sheet->id, 'orders' => count($orders), 'status' => true];
                    $newOrders = [...$newOrders, ...$orders];
                    $sheet->active = true;
                    $sheet->save();
                } catch (\Throwable $th) {
                    $errors[] = [
                        'sheet_id' => $sheet->id,
                        'message' => $th->getMessage(),
                        'file' => $th->getFile(),
                        'line' => $th->getLine(),
                        'trace' => $th->getTrace(),
                        'trace_string' => $th->getTraceAsString()
                    ];

                    $done[] = ['id' => $sheet->id, 'orders' => 0, 'status' => false];
                    if($th->getMessage() == 'PERMISSION_DENIED') {
                        $sheet->active = false;
                        $sheet->save();
                    }
                    continue;
                }
            }
            Auth::logout();
            return response()->json([
                'status' => true,
                'code' => 'SUCCESS',
                'errors' => $errors,
                'data' => [
                    'orders' => $newOrders,
                    'sheets' => $done
                ]
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'code' => 'SERVER_ERROR',
                'from' => 'GoogleSheetController',
                'error' => [
                    'file' => $th->getFile(),
                    'line' => $th->getLine(),
                    'trace' => $th->getTrace(),
                    'trace_string' => $th->getTraceAsString()
                ],
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function sync(Request $request) {

        try {
            if (!$request->user()->can('show_all_sheets')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See Sheets',
                    ],
                    405
                );
            }

            $sheets = Sheet::where('auto_fetch', 1)->get();
            $sheet_helper = new SheetHelper();

            $done = [];

            foreach($sheets as $sheet) {
                try {
                    $orders = $sheet_helper->sync_orders($sheet);
                    $done[] = ['id' => $sheet->id, 'orders' => count($orders), 'status' => true];
                    $sheet->active = true;
                    $sheet->save();
                } catch (\Throwable $th) {
                    $done[] = ['id' => $sheet->id, 'orders' => count($orders), 'status' => false];
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
                    'sheets' => $done
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

            if (!$request->user()->can('update_sheet')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Update Sheets',
                    ],
                    405
                );
            }
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

            if (!$request->user()->can('update_sheet')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Update Sheets',
                    ],
                    405
                );
            }

            $sheet = Sheet::find($id);

            if (!isset($sheet)) {

                return response()->json([
                    'status' => false,
                    'code' => 'NOT_FOUND',
                ]);
            }

            $sheet_helper = new SheetHelper();
            $orders = $sheet_helper->sync_orders($sheet);
            $sheet->active = true;
            $sheet->save();

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
