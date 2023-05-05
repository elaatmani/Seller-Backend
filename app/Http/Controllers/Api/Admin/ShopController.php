<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('show_all_shops')) {
            return response()->json(
                [
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To See Shops',
                ],
                405
            );
        }

        $shops = Shop::all();

        return response()->json(
            [
                'status' => true,
                'code' => 'SUCCESS',
                'data' => [
                    'shops' => $shops,
                ],
            ],
            200
        );
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

            if (!$request->user()->can('create_shop')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Create shop',
                    ],
                    405
                );
            }


            //Validated
            $shopValidator = Validator::make(
                $request->all(),
                [
                    'name' => 'string'
                ]
            );

            if ($shopValidator->fails()) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'validation error',
                        'error' => $shopValidator->errors()
                    ],
                    401
                );
            }

            DB::beginTransaction();
             $shop = Shop::create([
                'name' => $request->name
             ]);
            DB::commit();



            return response()->json([
                'status' => true,
                'code' => 'SHOP_CREATED',
                'message' => 'Shop Added Successfully!',
                'data' => $shop,
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
    public function show(Request  $request,$id)
    {
        try {
            if (!$request->user()->can('view_shop')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See Shop',
                    ],
                    405
                );
            }

            $shop = Shop::find($id);
            if (isset($shop)) {
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => [
                            'shops' => $shop,
                        ],
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_FOUND',
                        'message' => 'Shop Not Exist',
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

            if (!$request->user()->can('update_shop')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Update shop',
                    ],
                    405
                );
            }


            //Validated
            $shopValidator = Validator::make(
                $request->all(),
                [
                    'name' => 'string'
                ]
            );

            if ($shopValidator->fails()) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'validation error',
                        'error' => $shopValidator->errors()
                    ],
                    401
                );
            }
            $shop = Shop::find($id);

            if (!isset($shop)) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_FOUND',
                        'message' => 'Shop Not Exist',
                    ],
                    404
                );
            }
            DB::beginTransaction();
            

            $shop->name = $request->name;
            $shop->save();

            DB::commit();



            return response()->json([
                'status' => true,
                'code' => 'SHOP_UPDATED',
                'message' => 'Shop Updated Successfully!',
                'data' => $shop,
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

            if (!$request->user()->can('delete_shop')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Delete shop',
                    ],
                    405
                );
            }

            $shop = Shop::find($id);



            if (!isset($shop)) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_FOUND',
                        'message' => 'Shop Not Exist',
                    ],
                    404
                );
            }
    
           

           
            $shop->delete();



            return response()->json([
                'status' => true,
                'code' => 'SHOP_DELETED',
                'message' => 'Shop Deleted Successfully!',
                'data' => $shop,
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
