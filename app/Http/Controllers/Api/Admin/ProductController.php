<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\ProductHelper;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Http\Controllers\Controller;



use Illuminate\Support\Facades\Validator;
use App\Models\InventoryMovementVariation;
use App\Models\ProductAgente;
use App\Models\ProductDelivery;
use App\Models\ProductImage;
use App\Models\User;
use App\Repositories\ProductRepository;

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
        $products = Product::with('variations','variations.warehouse')->get()->map(fn($product) => ProductHelper::with_state($product));

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
            // $request->deliveries = json_decode($request->deliveries, true);
            // return response()->json($request->all());

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
                    'buying_price' => 'required',
                    'selling_price' => 'required',
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


            if ($request->hasFile('image')) {
                // Upload and associate the image with the product
                $image = $request->file('image');
                $uniqueId = Str::uuid()->toString();
                $extension = $image->getClientOriginalExtension();
                $imagePath = "productImages/{$uniqueId}.{$extension}";
                $image->storeAs('public', $imagePath);

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                ]);
            }

            $deliveries = json_decode($request->deliveries, true) ?? [];

            foreach($deliveries as $delivery ){

                ProductDelivery::Create([
                    'delivery_id' => $delivery['delivery_id'],
                    'product_id' => $product->id
                ]);
            }

            $variations = json_decode($request->variations, true) ?? [];

            $quantityTotal = 0;
            foreach ($variations as  $value) {
                ProductVariation::create([
                    'product_id' => $product->id,
                    'product_ref' => $product->ref,
                    'warehouse_id' => $value['warehouse_id'],
                    'size'  => $value['size'],
                    'color' => $value['color'],
                    'quantity' => $value['quantity'],
                    'stockAlert' => $value['stockAlert']
                ]);
                $quantityTotal += $value['quantity'];
            }
            $users = User::where('having_all',1)->get('id');
            if($users){
                foreach($users as $user){
                    ProductAgente::create([
                        'agente_id'=> $user->id,
                        'product_id' => $product->id
                    ]);
                }

            }

            $offers = json_decode($request->offers, true) ?? [];
            ProductRepository::createProductOffers($product->id, $offers);

            DB::commit();
            return response()->json(
                [
                    'status' => true,
                    'code' => 'PRODUCT_CREATED',
                    'message' => 'Product Created Successfully!',
                    'data' => [
                        'product' => ProductHelper::with_tracking(Product::find($product->id))
                    ]
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

            $product = Product::with('variations', 'variations.warehouse','deliveries')->find($id);
            if (isset($product)) {
                $product = ProductHelper::get_state($product);
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
                    'buying_price' => 'required',
                    'selling_price' => 'required',
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

            if ($request->hasFile('image')) {
                // Delete the existing product image from the database and storage
                $product->product_image()->delete();

                // Upload and associate the new image with the product
                $image = $request->file('image');
                $uniqueId = Str::uuid()->toString();
                $extension = $image->getClientOriginalExtension();
                $imagePath = "productImages/{$uniqueId}.{$extension}";
                $image->storeAs('public', $imagePath);

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                ]);
            }

            DB::beginTransaction();

            // Get all existing product deliveries for the given product
            $existingDeliveries = ProductDelivery::where('product_id', $product->id)->get();

            $deliveries = json_decode($request->deliveries, true) ?? [];
            // Get an array of delivery IDs from the new $request->deliveries
            $newDeliveryIds = array_column($deliveries, 'delivery_id');

            // Loop through existing deliveries and delete those that are not in the new array
            foreach ($existingDeliveries as $existingDelivery) {
                if (!in_array($existingDelivery->delivery_id, $newDeliveryIds)) {
                    $existingDelivery->delete();
                }
            }

            // Loop through the new $request->deliveries and update or create records accordingly
            foreach ($deliveries as $delivery) {
                ProductDelivery::updateOrCreate(
                    [
                        'delivery_id' => $delivery['delivery_id'],
                        'product_id' => $product->id
                    ],
                    [
                        'delivery_id' => $delivery['delivery_id'],
                        'product_id' => $product->id
                    ]
                );
            }

            $variations = json_decode($request->variations, true) ?? [];
            // all variations
            $all_variations = collect($variations);

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
                $variation->warehouse_id = $updated_variation['warehouse_id'];
                $variation->color = $updated_variation['color'];
                $variation->stockAlert = $updated_variation['stockAlert'];
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
                    'warehouse_id' => (int) $v['warehouse_id'],
                    'size' => $v['size'],
                    'color' => $v['color'],
                    'quantity' => (int) $v['quantity'],
                    'stockAlert' => (int) $v['stockAlert']
                ]);
            }

            $offers = json_decode($request->offers, true) ?? [];
            ProductRepository::updateProductOffers($product->id, $offers);

            DB::commit();

            return response()->json(
                [
                    'status' => true,
                    'code' => 'PRODUCT_UPDATED',
                    'message' => 'Product Updated Successfully!',
                    'data' => [
                        'product' => ProductHelper::with_tracking(Product::find($product->id))
                    ]
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
