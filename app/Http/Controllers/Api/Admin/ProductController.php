<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\InventoryState;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\ProductVariation;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('show_all_products')) {
            return response()->json(
                [
                    'status' => false,
                    'code' => 'NOT_ALLOWED',
                    'message' => 'You Dont Have Access To See Products',
                ],
                405
            );
        }
        $products = Product::with('variations')->get();

        return response()->json(
            [
                'status' => true,
                'code' => 'SUCCESS',
                'data' => [
                    'products' => $products,
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
    public function create(Request $request)
    {
        try {

            if (!$request->user()->can('create_product')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See Products',
                    ],
                    405
                );
            }

            //Validated
            $messages = [
                'ref.unique' => 'The Product Already Exist!'
            ];
            
            $validateProduct = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'ref' => 'required|unique:products,ref',
                    'buying_price' => 'required|integer',
                    'selling_price' => 'required|integer',
                ]
            );
            if ($validateProduct->fails()) {
                $errors = $validateProduct->errors();
                $response = [
                    'status' => false, 'code' => 'VALIDATION_ERROR', 'message' => 'Validation error', 'errors' => []
                ];
                if ($errors->has('ref')) {
                    $response['errors']['ref'] = $messages['ref.unique'];
                }
                return response()->json($response, 200);
            }

            DB::beginTransaction();
            $product = Product::create([
                'name' => $request->name,
                'ref' => $request->ref,
                'buying_price' => $request->buying_price,
                'selling_price' => $request->selling_price,
                'description' => $request->description,
                'status' => 1
            ]);

            $quantityTotal = 0;
            foreach ($request->variants as  $value) {
                ProductVariation::create([
                    'product_id' => $product->id,
                    'product_ref' => $product->ref,
                    'size'  => $value['size'],
                    'color' => $value['color'],
                    'quantity' => $value['quantity']
                ]);
                $quantityTotal += $value['quantity'];
            }

            InventoryState::create([
                'product_id' => $product->id,
                'quantity' => $quantityTotal,
            ]);
            DB::commit();
            return response()->json(
                [
                    'status' => true,
                    'code' => 'PRODUCT_CREATED',
                    'message' => 'Product Created Successfully!',
                ],
                200
            );
        } catch (\Throwable $th) {

            // rollback transaction on error
            DB::rollBack();

            // check if the error is due to duplicate reference
            if (strpos($th->getMessage(), 'Duplicate entry') !== false) {
                return response()->json([
                    'status' => true,
                    'code' => 'REFERENCE_DUPLICATED',
                    'message' => 'Product Reference already exists!',
                ], 200);
            }

            return response()->json(
                [
                    'status' => false,
                    'code' => 'SERVER_ERROR',
                    'message' => $th->getMessage(),
                    'error' => $validateProduct->errors()
                ],
                500
            );
        }
    }


    /**
     * Display the specified resource.
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            if (!$request->user()->can('view_product')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To See Product',
                    ],
                    405
                );
            }

            $product = Product::with('variations')->find($id);
            if (isset($product)) {
                return response()->json(
                    [
                        'status' => true,
                        'code' => 'SUCCESS',
                        'data' => [
                            'products' => $product,
                        ],
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_FOUND',
                        'message' => 'Product Not Exist',
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            if (!$request->user()->can('update_product')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Update Product',
                    ],
                    405
                );
            }

            $product = Product::findOrFail($id);



            if (isset($product)) {
                //Validated
                $productValidator = Validator::make(
                    $request->all(),
                    [
                        'name' => 'required',
                        'ref' => 'required|unique:products,ref,' . $product->id,
                        'buying_price' => 'required|integer',
                        'selling_price' => 'required|integer',
                    ]
                );

                if ($productValidator->fails()) {
                    return response()->json(
                        [
                            'status' => false,
                            'code' => 'VALIDATION_ERROR',
                            'message' => 'validation error',
                            'error' => $productValidator->errors()
                        ],
                        401
                    );
                }


                $product->name = $request->name;
                $product->ref = $request->ref;
                $product->buying_price = $request->buying_price;
                $product->selling_price = $request->selling_price;
                $product->description = $request->description;
                $product->status = 1;

                $product->save();


                $existingVariations = ProductVariation::where('product_id', $id)->get();
                $inventoryState = InventoryState::where('product_id', $id)->first();
                $inventoryMovement = InventoryMovement::where('product_id', $id)->sum('qty_to_delivery');



                if ($existingVariations->count() > 0) {
                    foreach ($existingVariations as $existingVariation) {
                        // Check if the size and color of the existing variation is not present in the $request object
                        if (!collect($request->input('variants'))->where('size', $existingVariation->size)->where('color', $existingVariation->color)->count()) {
                            // If the variation size and color does not exist in the $request object, add the quantity to the existing variation quantity variable
                            // Delete the variation
                            $existingVariation->delete();
                        }
                    }
                }




                if (count($request->variants) > 0) {

                    $quantityUpdated = 0;
                    foreach ($request->variants as $variant) {
                        $quantityUpdated += $variant['quantity'];
                    }

                    if ($inventoryMovement < $quantityUpdated) {


                        foreach ($request->input('variants') as $variant) {
                            ProductVariation::updateOrCreate(
                                [
                                    'product_id' => $id,
                                    'size' => $variant['size'],
                                    'color' => $variant['color'],
                                ],
                                [
                                    'product_id' => $id,
                                    'product_ref' => $product->ref,
                                    'size' => $variant['size'],
                                    'color' => $variant['color'],
                                    'quantity' => $variant['quantity'],
                                ]
                            );
                        }
                        $inventoryState->quantity = $quantityUpdated;
                        $inventoryState->save();

                        return response()->json(
                            [
                                'status' => true,
                                'code' => 'PRODUCT_UPDATED',
                                'message' => 'Product Updated Successfully!',
                            ],
                            200
                        );
                    } else {
                        return response()->json(
                            [
                                'status' => true,
                                'code' => 'PRODUCT_NOT_UPDATED',
                                'message' => 'MIN Qty to Add ' . $inventoryMovement + 1,
                            ],
                            200
                        );
                    }
                }
            } else {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_FOUND',
                        'message' => 'Product Not Exist',
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
     * Remove the specified resource from storage.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request, $id)
    {
        try {
            if (!$request->user()->can('delete_product')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Delete the Product',
                    ],
                    405
                );
            }


            $product = Product::find($id);
            if (isset($product)) {
                Product::where('id', $id)->delete();
                return response()->json([
                    'status' => true,
                    'code' => 'PRODUCT_DELETED',
                    'message' => 'Product Deleted Successfully!',
                    200
                ]);
            }
            return  response()->json([
                'status' => false,
                'code' => 'NOT_FOUND',
                'message' => 'Product Not Exist!',
                404
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'code' => 'SERVER_ERROR',
                    'message' => $th->getMessage(),
                ],
                500
            );
        }
    }








    // public function test(){
    //     $sheets = Sheets::spreadsheet(config('sheets.post_spreadsheet_id'))

    //     ->sheetById(config('sheets.post_sheet_id'))

    //     ->all();

    //     $sheet = array_slice($sheets, 1);

    //     $posts = array();

    //     foreach ($sheet AS $data) {

    //         $posts[] = array(

    //             'name' => $data[0],



    //         );

    //     }
    //     return response()->json([
    //         'status' => true,
    //         'code' => 'OORDERS_SUCCESS',
    //         'message' => 'Orders Fetched Successfully!',
    //         'data' => $posts,
    //         200
    //     ]);
    // }
}
