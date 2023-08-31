<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('show_all_ads')) {
            return response()->json(
                [
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To See Ads',
                ],
                405
            );
        }



        $ads = Ads::all();

        return response()->json(
            [
                'status' => true,
                'code' => 'SUCCESS',
                'data' => [
                    'ads' => $ads,
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

            if (!$request->user()->can('create_ads')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Create ads',
                    ],
                    405
                );
            }


            //Validated
            $adsValidator = Validator::make(
                $request->all(),
                [
                    'source' => 'string',
                    'amount' => 'float',
                    'ads_at' => 'date'
                ]
            );

            if ($adsValidator->fails()) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'validation error',
                        'error' => $adsValidator->errors()
                    ],
                    401
                );
            }

            DB::beginTransaction();
             $ads = Ads::create([
                'source' => $request->source,
                'amount' => $request->amount,
                'ads_at' => $request->ads_at
             ]);
            DB::commit();



            return response()->json([
                'status' => true,
                'code' => 'ADS_CREATED',
                'message' => 'Ads Added Successfully!',
                'data' => $ads,
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            if (!$request->user()->can('view_ads')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See Ads',
                    ],
                    405
                );
            }

            $ads = Ads::find($id);

            if (isset($ads)) {
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => [
                            'ads' => $ads,
                        ],
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_FOUND',
                        'message' => 'Ads Not Exist',
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

            if (!$request->user()->can('update_ads')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Update Ads',
                    ],
                    405
                );
            }

            $ads = Ads::find($id);

            if ($ads) {
                $ads->source = $request->source;
                $ads->amount = $request->amount;
                $ads->ads_at = $request->ads_at;

                $ads->save();
            }

            return response()->json(
                [
                    'status' => true,
                    'code' => 'ADS_UPDATED',
                    'message' => 'Ads Updated Successfully!',
                    'data' => [
                        'ads' => $ads,
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request ,$id)
    {
        try {
            if (!$request->user()->can('delete_ads')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Delete Ads',
                    ],
                    405
                );
            }

            $ads = Ads::find($id);
            if ($ads) {

                $ads->delete();
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'ADS_DELETED',
                        'message' => 'Ads Deleted Successfully!',
                    ],
                    200
                );

               
            }
            return response()->json(
                [
                    'status' => false,
                    'code' => 'NOT_FOUND',
                    'message' => 'Ads Not Exist',
                ],
                404
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
}
