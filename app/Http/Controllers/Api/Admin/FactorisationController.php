<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Models\Factorisation;
use App\Models\FactorisationFee;
use App\Http\Controllers\Controller;
// use PDF;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Storage;


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

        $factorisation = Factorisation::when(!auth()->user()->hasRole('admin'), function ($query) {
            return $query->where('user_id', auth()->id())->where('close', 1);
        })->with('delivery', 'seller', 'fees')->get();



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
     * update the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addOrUpdateFees(Request $request, $id)
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
                $existingFees = $factorisation->fees->pluck('feename')->toArray();

                foreach ($request->fees as $fee) {
                    FactorisationFee::updateOrCreate([
                        'factorisation_id' => $factorisation->id,
                        'feename' => $fee['feename'],
                    ], [
                        'feename' => $fee['feename'],
                        'feeprice' => $fee['feeprice']
                    ]);

                    // Remove the fee from the existingFees array
                    $key = array_search($fee['feename'], $existingFees);
                    if ($key !== false) {
                        unset($existingFees[$key]);
                    }
                }
                // Delete fees that are not present in $request->fees
                if (!empty($existingFees)) {
                    $fees = FactorisationFee::whereIn('feename', $existingFees)->get();
                    foreach ($fees as $f) {
                        $f->delete();
                    }
                }

                return response()->json(
                    [
                        'status' => true,
                        'code' => 'FACTORISATION_UPDATED',
                        'message' => 'Factorisation Updated Successfully!',
                    ],
                    200
                );
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
                    'code' => 'SERVER_ERROR',
                    'trace' => $th->getTrace()
                ],
                500
            );
        }
    }


    // /**
    //  * Display the specified resource.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function showFees(Request  $request, $id)
    // {
    //     try {
    //         if (!$request->user()->can('view_factorisation')) {
    //             return response()->json(
    //                 [
    //                     'status' => false,
    //                     'code' => 'NOT_ALLOWED',
    //                     'message' => 'You Dont Have Access To See Factorisation',
    //                 ],
    //                 405
    //             );
    //         }

    //         $factorisation = Factorisation::with('delivery')->find($id);

    //         if (isset($factorisation)) {
    //             return response()->json(
    //                 [
    //                     'status' => true,
    //                     'code' => 'SUCCESS',
    //                     'data' => [
    //                         'factorisations' => $factorisation,
    //                     ],
    //                 ],
    //                 200
    //             );
    //         } else {
    //             return response()->json(
    //                 [
    //                     'status' => false,
    //                     'code' => 'NOT_FOUND',
    //                     'message' => 'factorisation Not Exist',
    //                 ],
    //                 404
    //             );
    //         }
    //     } catch (\Throwable $th) {
    //         return response()->json(
    //             [
    //                 'status' => false,
    //                 'code' => 'SERVER_ERROR',
    //                 'message' => $th->getMessage()
    //             ],
    //             500
    //         );
    //     }
    // }


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
                if($factorisation->paid) {
                    return response()->json(
                        [
                            'status' => false,
                            'message' => 'Cannot unclose a paid invoice.',
                            'code' => 'ERROR'
                        ],
                        500
                    );
                }


                $factorisation->close = $request->close;
                if ($request->close == true) {
                    $factorisation->close_at = now();
                } else {
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
                if ($request->paid == true) {
                    $factorisation->paid_at = now();
                    if ($factorisation->type == "seller") {
                        $orders = Order::where('seller_factorisation_id', $id)->get();
                        foreach($orders as $order) {
                            $order->delivery = 'paid';
                            $order->save();
                        }
                    }
                } else {
                    $factorisation->paid_at = null;
                    if ($factorisation->type == "seller") {
                        $orders = Order::where('seller_factorisation_id', $id)->get();
                        foreach($orders as $order) {
                            $order->delivery = 'livrer';
                            $order->save();
                        }
                    }
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


        $factorisation = Factorisation::with('seller', 'delivery', 'delivery.deliveryPlaces', 'delivery.deliveryPlaces.city', 'fees')
            ->where('id', $id)
            ->first(); // Retrieve the user based on the ID

        $salesDelivery = Order::with('items', 'items.product')
            ->where('factorisation_id', $factorisation->id)
            ->get();


        $salesSeller = Order::with('delivery_user', 'delivery_user.deliveryPlaces', 'delivery_user.deliveryPlaces.city', 'items', 'items.product')
            ->where('seller_factorisation_id', $factorisation->id)
            ->get();

        // dd($salesSeller);

        // dd($salesSeller);
        // $factorisation = $factorisation->chunk(20);
        // $sales = $sales->chunk(20);

        $headers = [
            'Content-Type' => 'application/pdf',
            'charset' => 'UTF-8'
        ];
        // return view('factorisationpdf')->with(compact('factorisation','sales'));
        if ($factorisation->type == "delivery") {
            $pdf = PDF::loadView('factorisationpdf', compact('factorisation', 'salesDelivery'));
        } else {
            $pdf = PDF::loadView('factorisationsellerpdf', compact('factorisation', 'salesSeller'));
        }


        return  $pdf->stream($factorisation->factorisation_id . '.pdf', 'UTF-8');
    }


    public function updateImageAttachement(Request $request, $id)
    {


        $factorisation = Factorisation::where('id', $id)->first();

        if (!$factorisation) return response()->json([
            'code' => 'NOT_FOUND',
        ]);



        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $imageName = 'invoice_' . $id . '_' . time() . '.' . $image->getClientOriginalExtension();

            // Store the image in the storage/uploads directory
            $storedImagePath = $image->storeAs('public/uploads/invoices', $imageName);

            // Get the full path without including the domain
            $imagePath = Storage::url($storedImagePath);

            $factorisation->attachement_image = $imagePath;
            $factorisation->save();

            return response()->json(['code' => 'SUCCESS', 'image' => $imageName, 'factorisation' => $factorisation]);
        }

        return response()->json([
            'message' => 'Image is required',
            'code' => 'VALIDATION_ERROR'
        ], 422);
    }
}
