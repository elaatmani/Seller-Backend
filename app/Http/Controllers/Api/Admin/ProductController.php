<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\ProductVariation;

use Revolution\Google\Sheets\Facades\Sheets;

use Illuminate\Http\Request;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('product_show')) {
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
            if (!$request->user()->can('product_create')) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_ALLOWED',
                        'message' => 'You Dont Have Access To Create Product',
                    ],
                    405
                );
            }


            //Validated
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
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'validation error',
                        'error' => $validateProduct->errors()
                    ],
                    401
                );
            }

            $product = Product::create([
                'name' => $request->name,
                'ref' => $request->ref,
                'buying_price' => $request->buying_price,
                'selling_price' => $request->selling_price,
                'description' => $request->description,
                'status' => 1
            ]);


            foreach ($request->variants as  $value) {
                ProductVariation::create([
                    'product_id' => $product->id,
                    'product_ref' => $product->ref,
                    'size'  => $value['size'],
                    'color' => $value['color'],
                    'quantity' => $value['quantity']
                ]);
            }

            return response()->json(
                [
                    'status' => true,
                    'code' => 'PRODUCT_CREATED',
                    'message' => 'Product Created Successfully!',
                ],
                200
            );
        } catch (\Throwable $th) {
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
            if (!$request->user()->can('product_show')) {
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
            if (!$request->user()->can('product_update')) {
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

                foreach ($existingVariations as $existingVariation) {
                    // Check if the ID of the existing variation is not present in the $request object
                    if (!collect($request->input('variants'))->pluck('id')->contains($existingVariation->id)) {
                        // If the variation ID does not exist in the $request object, delete the variation
                        $existingVariation->delete();
                    }
                }
                
                if ($request->has('variants')) {
                    foreach ($request->input('variants') as $variant) {
                        ProductVariation::updateOrCreate(
                            ['id' => $variant['id']],
                            [
                                'product_id' => $id,
                                'product_ref' => $product->ref,
                                'size' => $variant['size'],
                                'color' => $variant['color'],
                                'quantity' => $variant['quantity'],
                            ]
                        );
                    }
                }


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
            if (!$request->user()->can('product_delete')) {
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








    public function test(){
        $sheets = Sheets::spreadsheet(config('sheets.post_spreadsheet_id'))

        ->sheetById(config('sheets.post_sheet_id'))

        ->all();

        $sheet = array_slice($sheets, 1);

        $posts = array();

        foreach ($sheet AS $data) {

            $posts[] = array(

                'name' => $data[0],
                


            );

        }
        return response()->json([
            'status' => true,
            'code' => 'OORDERS_SUCCESS',
            'message' => 'Orders Fetched Successfully!',
            'data' => $posts,
            200
        ]);
    }
}
