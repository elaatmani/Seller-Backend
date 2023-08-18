<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Sheet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Add permission
        // if (!$request->user()->can('show_all_sheets')) {
        //     return response()->json(
        //         [
        //             'status' => false,
        //             'code' => 'NOT_ALLOWED',
        //             'message' => 'You Dont Have Access To See Google Sheets',
        //         ],
        //         405
        //     );
        // }

        $sheets = Sheet::when(!auth()->user()->hasRole('admin'), function ($query) {
            return $query->where('user_id', auth()->id());
        })->get();

        return response()->json(
            [
                'status' => true,
                'code' => 'SUCCESS',
                'data' => [
                    'sheets' => $sheets,
                ],
            ],
            200
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            // Add permission
            // if (!$request->user()->can('create_sheet')) {
            //     return response()->json(
            //         [
            //             'status' => false,
            //             'code' => 'NOT_ALLOWED',
            //             'message' => 'You Dont Have Access To Create Sale',
            //         ],
            //         405
            //     );
            // }


            //Validated
            $sheetValidator = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'sheet_id' => 'required',
                    'sheet_name' => 'required',
                    'auto_fetch' => 'required',
                ]
            );

            if ($sheetValidator->fails()) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'validation error',
                        'error' => $sheetValidator->errors()
                    ],
                    401
                );
            }

            $sheet = Sheet::create([...$sheetValidator->validated(),'user_id'=>auth()->id()]);

            return response()->json([
                'status' => true,
                'code' => 'SUCCESS',
                'message' => 'Sheet Added Successfully!',
                'data' => [
                    'sheet' => $sheet
                ]
            ], 200);
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


    /**
     *
     * @return \Illuminate\Http\Response
     */
    public function updateAutoFetch(Request $request, $id)
    {
        try {

            // Add permission
            // if (!$request->user()->can('update_sheet')) {
            //     return response()->json(
            //         [
            //             'status' => false,
            //             'code' => 'NOT_ALLOWED',
            //             'message' => 'You Dont Have Access To Create Sale',
            //         ],
            //         405
            //     );
            // }


            $sheet = Sheet::find($id);

            if (!isset($sheet)) {

                return response()->json([
                    'status' => false,
                    'code' => 'NOT_FOUND',
                ]);
            }

            $sheet->auto_fetch = $request->autoFetch == 'true' ? 1 : 0;
            $sheet->save();

            return response()->json(
                [
                    'status' => true,
                    'code' => 'SUCCESS',
                    'message' => 'Auto fetch Updated Successfully!',
                    'data' => [
                        'sheet' => $sheet,
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {

            // Add permission
            // if (!$request->user()->can('create_sheet')) {
            //     return response()->json(
            //         [
            //             'status' => false,
            //             'code' => 'NOT_ALLOWED',
            //             'message' => 'You Dont Have Access To Create Sale',
            //         ],
            //         405
            //     );
            // }

            $sheet = sheet::find($id);

            if (!isset($sheet)) {

                return response()->json([
                    'status' => false,
                    'code' => 'NOT_FOUND',
                ]);
            }

            //Validated
            $sheetValidator = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'sheet_id' => 'required',
                    'sheet_name' => 'required',
                    'auto_fetch' => 'required',
                ]
            );

            if ($sheetValidator->fails()) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'validation error',
                        'error' => $sheetValidator->errors()
                    ],
                    401
                );
            }

            $sheet->name = $request->name;
            $sheet->sheet_id = $request->sheet_id;
            $sheet->sheet_name = $request->sheet_name;
            $sheet->auto_fetch = $request->auto_fetch;
            $sheet->save();

            return response()->json([
                'status' => true,
                'code' => 'SUCCESS',
                'message' => 'Sheet updated Successfully!',
                'data' => [
                    'sheet' => $sheet
                ]
            ], 200);
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {

            // Add permission
            // if (!$request->user()->can('create_sheet')) {
            //     return response()->json(
            //         [
            //             'status' => false,
            //             'code' => 'NOT_ALLOWED',
            //             'message' => 'You Dont Have Access To Create Sale',
            //         ],
            //         405
            //     );
            // }

            $sheet = sheet::find($id);

            if (!isset($sheet)) {

                return response()->json([
                    'status' => false,
                    'code' => 'NOT_FOUND',
                ]);
            }

            $sheet->delete();

            return response()->json([
                'status' => true,
                'code' => 'SUCCESS',
                'message' => 'Sheet deleted Successfully!'
            ], 200);

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
