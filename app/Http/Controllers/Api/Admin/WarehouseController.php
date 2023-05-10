<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {

            if (!$request->user()->can('show_all_warehouses')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See Warehouses',
                    ],
                    405
                );
            }

            $warehouses = Warehouse::all();

                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => [
                            'warehouses' => $warehouses,
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            if (!$request->user()->can('create_warehouse')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Create warehouse',
                    ],
                    405
                );
            }


            //Validated
            $warehouseValidator = Validator::make(
                $request->all(),
                [
                    'name' => 'string'
                ]
            );

            if ($warehouseValidator->fails()) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'validation error',
                        'error' => $warehouseValidator->errors()
                    ],
                    401
                );
            }

            DB::beginTransaction();
            $warehouse = Warehouse::create([
                'name' => $request->name
            ]);
            DB::commit();



            return response()->json([
                'status' => true,
                'code' => 'WAREHOUSE_CREATED',
                'message' => 'Warehouse Added Successfully!',
                'data' =>[
                    'warehouse' => $warehouse
                ] ,
                
                200
            ]);
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
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request  $request, $id)
    {
        try {
            if (!$request->user()->can('view_warehouse')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See Warehouse',
                    ],
                    405
                );
            }

            $warehouse = Warehouse::find($id);
            if (isset($warehouse)) {
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => [
                            'warehouses' => $warehouse,
                        ],
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_FOUND',
                        'message' => 'Warehouse Not Exist',
                    ],
                    404
                );
            }
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {

            if (!$request->user()->can('update_warehouse')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Update warehouse',
                    ],
                    405
                );
            }


            //Validated
            $warehouseValidator = Validator::make(
                $request->all(),
                [
                    'name' => 'string'
                ]
            );

            if ($warehouseValidator->fails()) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'validation error',
                        'error' => $warehouseValidator->errors()
                    ],
                    401
                );
            }
            $warehouse = Warehouse::find($id);

            if (!isset($warehouse)) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_FOUND',
                        'message' => 'Warehouse Not Exist',
                    ],
                    404
                );
            }
            DB::beginTransaction();


            $warehouse->name = $request->name;
            $warehouse->save();

            DB::commit();



            return response()->json([
                'status' => true,
                'code' => 'WAREHOUSE_UPDATED',
                'message' => 'Warehouse Updated Successfully!',
                'data' => [ 'warehouse' => $warehouse],
                200
            ]);
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

            if (!$request->user()->can('delete_warehouse')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Delete warehouse',
                    ],
                    405
                );
            }

            $warehouse = Warehouse::find($id);



            if (!isset($warehouse)) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_FOUND',
                        'message' => 'Warehouse Not Exist',
                    ],
                    404
                );
            }




            $warehouse->delete();



            return response()->json([
                'status' => true,
                'code' => 'WAREHOUSE_DELETED',
                'message' => 'Warehouse Deleted Successfully!',
                'data' => $warehouse,
                200
            ]);
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
