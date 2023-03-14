<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\InventoryState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{


   
   /**
    * Display Inventory States.
    *
    * @return \Illuminate\Http\Response
    */
   public function inventoryState(Request $request)
   {
      try {
         if (!$request->user()->can('show_all_inventory_states')) {
            return response()->json(
               [
                  'status' => false,
                  'code' => 'NOT_ALLOWED',
                  'message' => 'You Dont Have Access To See Product',
               ],
               405
            );
         }
         $inventoryState = InventoryState::with('product')->get();

         return response()->json([
            'status' => true,
            'code' => 'SHOW_ALL_INVENTORY_STATES',
            'data' => $inventoryState
         ], 200);
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
    * Display Inventory Movements. 
    *
    * @return \Illuminate\Http\Response
    */
   public function inventoryMovement(Request $request)
   {
      try {
         if (!$request->user()->can('show_all_inventory_movements')) {
            return response()->json(
               [
                  'status' => false,
                  'code' => 'NOT_ALLOWED',
                  'message' => 'You Dont Have Access To See Product',
               ],
               405
            );
         }
         $inventoryMovement = InventoryMovement::with('product', 'delivery')->get();
         return response()->json([
            'status' => true,
            'code' => 'SHOW_ALL_INVENTORY_MOVEMENTS',
            'data' => $inventoryMovement
         ], 200);
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
    * Create Inventory Movement. 
    *
    * @return \Illuminate\Http\Response
    */
   public function createInventoryMovement(Request $request)
   {
      try {
         if (!$request->user()->can('create_inventory_movement')) {
            return response()->json(
               [
                  'status' => false,
                  'code' => 'NOT_ALLOWED',
                  'message' => 'You Dont Have Access To See Product',
               ],
               405
            );
         }
         //Validated
         $validateInventoryMovement = Validator::make(
            $request->all(),
            [
               'product_id' => 'required|integer',
               'delivery_id' => 'required|integer',
               'quantity' => 'required|integer',
            ]
         );

         if ($validateInventoryMovement->fails()) {
            return response()->json(
               [
                  'status' => false,
                  'code' => 'VALIDATION_ERROR',
                  'message' => 'validation error',
                  'error' => $validateInventoryMovement->errors()
               ],
               401
            );
         }
         $inventoryState = InventoryState::where('product_id', $request->product_id)->get()->first();

         if ($inventoryState) {
            if ($inventoryState->quantity >= $request->quantity) {
               InventoryMovement::create([
                  'product_id' => $request->product_id,
                  'delivery_id' => $request->delivery_id,
                  'qty_to_delivery' => $request->quantity,
               ]);

               $currentTotal = $inventoryState->quantity - $request->quantity;
               $inventoryState->quantity = $currentTotal;
               $inventoryState->save();

               return response()->json([
                  'status' => true,
                  'code' => 'SUCCESS',
                  'message' => 'Inventory Movement Added Successfully!'
               ], 200);
            } else {
               return response()->json([
                  'status' => true,
                  'code' => 'ERROR_QUANTITY',
                  'message' => 'Max quantity is ' . $inventoryState->quantity,
               ], 200);
            }
         }
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
    * Update Inventory Movement. 
    *
    * @return \Illuminate\Http\Response
    */
   public function updateInventoryMovement(Request $request, $id)
   {
      try {
         if (!$request->user()->can('update_inventory_movement')) {
            return response()->json(
               [
                  'status' => false,
                  'code' => 'NOT_ALLOWED',
                  'message' => 'You Dont Have Access To See Product',
               ],
               405
            );
         }
         //Validated
         $validateInventoryMovement = Validator::make(
            $request->all(),
            [
               'product_id' => 'required|integer',
               'delivery_id' => 'required|integer',
               'quantity' => 'required|integer',
            ]
         );

         if ($validateInventoryMovement->fails()) {
            return response()->json(
               [
                  'status' => false,
                  'code' => 'VALIDATION_ERROR',
                  'message' => 'validation error',
                  'error' => $validateInventoryMovement->errors()
               ],
               401
            );
         }

         $inventoryMovement = InventoryMovement::find($id);
         if ($inventoryMovement) {
            $inventoryState = InventoryState::where('product_id', $inventoryMovement->product_id)->get()->first();
            if ($inventoryState) {
               $totalQuantity = $inventoryState->quantity + $inventoryMovement->qty_to_delivery;
               if ($totalQuantity >= $request->quantity) {
                  $currentTotal = $totalQuantity - $request->quantity;

                  $inventoryMovement->product_id = $request->product_id;
                  $inventoryMovement->delivery_id = $request->delivery_id;
                  $inventoryMovement->qty_to_delivery = $request->quantity;
                  $inventoryMovement->save();

                  $inventoryState->quantity = $currentTotal;
                  $inventoryState->save();

                  return response()->json([
                     'status' => true,
                     'code' => 'SUCCESS',
                     'message' => 'Inventory Updated Successfully !',
                  ], 200);
               }

               return response()->json([
                  'status' => true,
                  'code' => 'ERROR_QUANTITY',
                  'message' => 'Max quantity is ' . $totalQuantity,
               ], 200);
            }

            return response()->json(
               [
                  'status' => false,
                  'code' => 'NOT_FOUND',
                  'message' => 'Inventory State Not Exist',
               ],
               404
            );
         }

         return response()->json(
            [
               'status' => false,
               'code' => 'NOT_FOUND',
               'message' => 'Inventory Movement Not Exist',
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
    * Delete Inventory Movement. 
    *
    * @return \Illuminate\Http\Response
    */
   public function deleteInventoryMovement(Request $request, $id)
   {
      try {
         if (!$request->user()->can('delete_inventory_movement')) {
            return response()->json(
               [
                  'status' => false,
                  'code' => 'NOT_ALLOWED',
                  'message' => 'You Dont Have Access To See Product',
               ],
               405
            );
         }

         $inventoryMovement = InventoryMovement::find($id);
         if ($inventoryMovement) {
            $inventoryState = InventoryState::where('product_id', $inventoryMovement->product_id)->get()->first();
            if ($inventoryState) {
               $totalQuantity = $inventoryState->quantity + $inventoryMovement->qty_to_delivery;

               $inventoryState->quantity = $totalQuantity;
               $inventoryState->save();


               $inventoryMovement->delete();

               return response()->json([
                  'status' => true,
                  'code' => 'SUCCESS',
                  'message' => 'Inventory Deleted Successfully !',
               ], 200);
            }

            return response()->json(
               [
                  'status' => false,
                  'code' => 'NOT_FOUND',
                  'message' => 'Inventory State Not Exist',
               ],
               404
            );
         }

         return response()->json(
            [
               'status' => false,
               'code' => 'NOT_FOUND',
               'message' => 'Inventory Movement Not Exist',
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
