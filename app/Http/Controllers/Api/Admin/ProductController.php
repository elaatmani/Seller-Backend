<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\ProductHelper;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;



use Illuminate\Support\Facades\Validator;
use App\Models\InventoryMovementVariation;

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
        $products = Product::with('variations')->get()->map(fn($product) => ProductHelper::with_state($product));

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
            foreach ($request->variations as  $value) {
                ProductVariation::create([
                    'product_id' => $product->id,
                    'product_ref' => $product->ref,
                    'size'  => $value['size'],
                    'color' => $value['color'],
                    'quantity' => $value['quantity']
                ]);
                $quantityTotal += $value['quantity'];
            }

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
                $product = ProductHelper::with_state($product);
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



            if (!isset($product)) {
                return response()->json(
                    [
                        'status' => false,
                        'code' => 'NOT_FOUND',
                        'message' => 'Product Not Exist',
                    ],
                    404
                );
            }

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

            DB::beginTransaction();
            // all variations
            $all_variations = collect($request->variations);

            // old variations
            $old_variations = $product->variations;

            // select only new added variation from request
            $new_variations = array_values($all_variations->whereNotIn('id', $old_variations->pluck('id'))->all());

            // 1 - handle deleted variations
            $deleted_variations = array_values($old_variations->whereNotIn('id', $all_variations->pluck('id'))->all());

            foreach ($deleted_variations as $v) {
                $exists = InventoryMovementVariation::where('product_variation_id', $v->id)->first();
                if(isset($exists)) {
                    return response()->json([
                        [
                            'status' => false,
                            'code' => 'QUANTITY_ERROR',
                            'message' => "Variation '$v->size / $v->color' can't be deleted. Already in movements"
                        ],
                        200
                    ]);
                }
                $v->delete();
            }

            // get product with it's current state
            $product = ProductHelper::with_state(Product::findOrFail($product->id));


            // 2 - handle update existing variations
            // loop through variations with their state
            foreach($product->variations as $v) {
                // get the actual variation
                $variation = ProductVariation::find($v->id);
                // get the same variation but from the request
                $updated_variation = $all_variations->where('id', $v->id)->first();

                // update the old variation with new values
                $variation->size = $updated_variation['size'];
                $variation->color = $updated_variation['color'];

                // return response()->json([
                //     'updated' => $updated_variation['quantity'],
                //     'available' => $v->available_quantity,
                //     'quantity' => $v->quantity,
                //     'calc' => ($updated_variation['quantity'] - $v->quantity)

                // ]);

                $used_quantity = $v->quantity - $v->available_quantity;

                // check if the new quantity is great or equal to the old quantity so it doesn't make problems
                if(($updated_variation['quantity'] != $v->quantity) && ((int) $updated_variation['quantity'] < $used_quantity )) {
                    return response()->json(
                        [
                            'status' => false,
                            'code' => 'QUANTITY_ERROR',
                            'message' => "Quantity of variation '$v->size / $v->color' should be greater than ". $used_quantity
                        ],
                        200
                    );
                }

                $variation->quantity = (int) $updated_variation['quantity'];
                $variation->save();
            }


            // 3 - handle new variations
            foreach($new_variations as $v) {
                ProductVariation::create([
                    'product_id' => $product->id,
                    'product_ref' => $product->ref,
                    'size' => $v['size'],
                    'color' => $v['color'],
                    'quantity' => (int) $v['quantity']
                ]);
            }

            DB::commit();

            return response()->json(
                [
                    'status' => true,
                    'code' => 'PRODUCT_UPDATED',
                    'message' => 'Product Updated Successfully!',
                    'data' => ProductHelper::with_state(Product::find($product->id))
                ],
                200
            );
        } catch (\Throwable $th) {
            DB::rollback();
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
