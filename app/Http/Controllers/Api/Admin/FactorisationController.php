<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Factorisation;
use App\Models\Order;
use App\Models\OrderItem;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
// use PDF;
use Illuminate\Http\Request;


class FactorisationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('show_all_factorisations')) {
            return response()->json(
                [
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To See Factorisation',
                ],
                405
            );
        }

        $factorisation = Factorisation::with('delivery')->get();

        return response()->json(
            [
                'status' => true,
                'code' => 'SUCCESS',
                'data' => [
                    'factorisations' => $factorisation,
                ],
            ],
            200
        );
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
            if (!$request->user()->can('view_factorisation')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See Factorisation',
                    ],
                    405
                );
            }

            $factorisation = Factorisation::with('delivery')->find($id);

            if (isset($factorisation)) {
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => [
                            'factorisations' => $factorisation,
                        ],
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_FOUND',
                        'message' => 'factorisation Not Exist',
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

            if (!$request->user()->can('update_factorisation')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Update Factorisation',
                    ],
                    405
                );
            }

            $factorisation = Factorisation::find($id);

            if ($factorisation) {
                $factorisation->factorisation_id = $request->factorisation_id;
                $factorisation->delivery_id = $request->delivery_id;
                $factorisation->commands_number = $request->commands_number;
                $factorisation->price = $request->price;
                $factorisation->note = $request->note;

                $factorisation->save();
            }

            return response()->json(
                [
                    'status' => true,
                    'code' => 'FACTORISATION_UPDATED',
                    'message' => 'Factorisation Updated Successfully!',
                    'data' => [
                        'factorisation' => $factorisation,
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateComment(Request $request, $id)
    {
        try {

            if (!$request->user()->can('update_factorisation')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Update Factorisation',
                    ],
                    405
                );
            }

            $factorisation = Factorisation::find($id);

            if ($factorisation) {
                $factorisation->comment = $request->comment;
                $factorisation->save();
            }

            return response()->json(
                [
                    'status' => true,
                    'code' => 'FACTORISATION_UPDATED',
                    'message' => 'Comment Updated Successfully!',
                    'data' => [
                        'factorisation' => $factorisation,
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function updateClosing(Request $request, $id)
    {
        try {

            if (!$request->user()->can('update_factorisation')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Update Factorisation',
                    ],
                    405
                );
            }

            $factorisation = Factorisation::find($id);

            if ($factorisation) {
                $factorisation->close = $request->close;
                if($request->close == true){
                    $factorisation->close_at = now();
                }else{
                    $factorisation->close_at = null;
                }
                $factorisation->save();
            }

            return response()->json(
                [
                    'status' => true,
                    'code' => 'FACTORISATION_UPDATED',
                    'message' => 'Closing Updated Successfully!',
                    'data' => [
                        'factorisation' => $factorisation,
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePayment(Request $request, $id)
    {
        try {

            if (!$request->user()->can('update_factorisation')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Update Factorisation',
                    ],
                    405
                );
            }

            $factorisation = Factorisation::find($id);

            if ($factorisation) {
                $factorisation->paid = $request->paid;
                if($request->paid == true){
                    $factorisation->paid_at = now();
                }else{
                    $factorisation->paid_at = null;
                }
                $factorisation->save();
            }

            return response()->json(
                [
                    'status' => true,
                    'code' => 'FACTORISATION_UPDATED',
                    'message' => 'Payment Updated Successfully!',
                    'data' => [
                        'factorisation' => $factorisation,
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
    public function destroy(Request $request, $id)
    {
        try {
            if (!$request->user()->can('delete_factorisation')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Delete Factorisation',
                    ],
                    405
                );
            }

            $factorisation = Factorisation::find($id);
            if ($factorisation) {

                    $factorisation->delete();
                    return response()->json(
                        [
                            'status' => true,
                            'code' => 'FACTORISATION_DELETED',
                            'message' => 'Factorisation Deleted Successfully!',
                        ],
                        200
                    );

                //  else {
                //     return response()->json(
                //         [
                //             'status' => false,
                //             'code' => 'DELETE_ERROR',
                //             'message' => "Warning! There is " . ($factorisation->commands_number === 1 ? $factorisation->commands_number . " Command left" : $factorisation->commands_number . " Commands left")
                //         ],
                //         200
                //     );
                // }
            }
            return response()->json(
                [
                    'status' => false,
                    'code' => 'NOT_FOUND',
                    'message' => 'Factorisation Not Exist',
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








    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function generatePDF($id)
    {

        $factorisation = Factorisation::with('delivery','delivery.deliveryPlaces','delivery.deliveryPlaces.city')->where('id',$id)->first(); // Retrieve the user based on the ID
        // dd($factorisation);
        $sales = Order::with('items','items.product')->where('factorisation_id',$factorisation->id)->get();



        $headers = [
            'Content-Type' => 'application/pdf',
        ];
        // return view('factorisationpdf')->with(compact('factorisation','sales'));
        $pdf = PDF::loadView('factorisationpdf', compact('factorisation','sales'));
        return  $pdf->stream();

    }

}
