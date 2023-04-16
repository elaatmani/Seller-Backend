<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\InventoryMovementVariation;
use App\Models\InventoryState;
use App\Models\InventoryStateVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                  'message' => 'You Dont Have Access To See Inventory States',
               ],
               405
            );
         }
         $inventoryState = InventoryState::with('product', 'inventoryStateVariations')->get();

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
    * Display Inventory States.
    *
    * @return \Illuminate\Http\Response
    */
   public function showInventoryState(Request $request, $id)
   {

      try {
         if (!$request->user()->can('show_all_inventory_states')) {
            return response()->json(
               [
                  'status' => false,
                  'code' => 'NOT_ALLOWED',
                  'message' => 'You Dont Have Access To See Inventory States',
               ],
               405
            );
         }
         $inventoryState = InventoryState::where('id', $id)->with('product', 'inventoryStateVariations')->get();

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
                  'message' => 'You Dont Have Access To See Inventory Movements',
               ],
               405
            );
         }
         if ($request->user()->roles->first()->id === 3) {
            $inventoryMovement =  InventoryMovement::where('delivery_id', $request->user()->id)->with('product', 'delivery.city', 'inventoryMovementVariations')->get();
         } else {
            $inventoryMovement = InventoryMovement::with('product', 'delivery.city','inventoryMovementVariations')->get();
         }
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
    * Show Inventory Movement.
    *
    * @return \Illuminate\Http\Response
    */
   public function showInventoryMovement(Request $request, $id)
   {
      try {
         if (!$request->user()->can('view_inventory_movement')) {
            return response()->json(
               [
                  'status' => false,
                  'code' => 'NOT_ALLOWED',
                  'message' => 'You Dont Have Access To See Product',
               ],
               405
            );
         }

         $inventoryMovement = InventoryMovement::where('id', $id)->with('product', 'delivery.city', 'inventoryMovementVariations')->get()->first();

         if ($inventoryMovement) {

            return response()->json([
               'status' => true,
               'code' => 'SUCCESS',
               'data' => [
                  'movement' => $inventoryMovement
               ]
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

               DB::beginTransaction();
               $inventoryMovement = InventoryMovement::create([
                  'product_id' => $request->product_id,
                  'delivery_id' => $request->delivery_id,
               ]);
               $quantity = 0;
               foreach ($request->variants as $variant) {
                  InventoryMovementVariation::create([
                     'inventory_movement_id' => $inventoryMovement->id,
                     'size' => $variant['size'],
                     'color' => $variant['color'],
                     'quantity' => $variant['quantity'],
                  ]);
                  $quantity += $variant['quantity'];
               }

               $currentTotal = $inventoryState->quantity - $quantity;
               $inventoryState->quantity = $currentTotal;
               $inventoryState->save();
               DB::commit();
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







   public function updateInventoryMovement(Request $request, $id)
   {
      try {
         if (!$request->user()->can('update_inventory_movement')) {
            return response()->json(
               [
                  'status' => false,
                  'code' => 'NOT_ALLOWED',
                  'message' => 'You do not have access to update inventory movement',
               ],
               405
            );
         }

         $validatedData = $request->validate([
            'product_id' => 'required|integer',
            'delivery_id' => 'required|integer',
            'variants.*.size' => 'required|string',
            'variants.*.color' => 'required|string',
            'variants.*.quantity' => 'required|integer'
         ]);

         $inventoryMovement = InventoryMovement::find($id);

         if (!$inventoryMovement) {
            return response()->json(
               [
                  'status' => false,
                  'code' => 'NOT_FOUND',
                  'message' => 'Inventory Movement Not Found',
               ],
               404
            );
         }

         $existingVariations = InventoryMovementVariation::where('inventory_movement_id', $inventoryMovement->id)->get();

         foreach ($existingVariations as $existingVariation) {
            $foundVariation = collect($validatedData['variants'])
               ->first(function ($v) use ($existingVariation) {
                  return $v['size'] === $existingVariation->size &&
                     $v['color'] === $existingVariation->color;
               });

            if (!$foundVariation) {

               $inventoryState = InventoryState::where('product_id', $validatedData['product_id'])->first();
               $inventoryStateVariations = InventoryStateVariation::where([
                  'inventory_state_id' => $inventoryState->id,
                  'size' => $existingVariation['size'],
                  'color' => $existingVariation['color']
               ])->first();
               $inventoryStateVariations->quantity += $existingVariation->quantity;
               $inventoryStateVariations->save();
               $existingVariation->delete();
            }
         }

         $inventoryState = InventoryState::where('product_id', $validatedData['product_id'])->first();


         if (!$inventoryState) {
            return response()->json(
               [
                  'status' => false,
                  'code' => 'NOT_FOUND',
                  'message' => 'Inventory State Not Found',
               ],
               404
            );
         }

         foreach ($validatedData['variants'] as $variant) {
            $existingVariant = InventoryStateVariation::where([
               'inventory_state_id' => $inventoryState->id,
               'size' => $variant['size'],
               'color' => $variant['color']
            ])->first();

            $existingVariantMovement = InventoryMovementVariation::where([
               'inventory_movement_id' => $inventoryMovement->id,
               'size' => $variant['size'],
               'color' => $variant['color']
            ])->first();

            if ($existingVariant->size && $existingVariantMovement->size) {
               $stockeQuantity = $existingVariant->quantity + $existingVariantMovement->quantity;
               if ($stockeQuantity < $variant['quantity']) {
                  return response()->json(
                     [
                        'status' => true,
                        'code' => 'QUANTITY',
                        'message' => 'MAX quantity is ' . $stockeQuantity,
                     ],
                     200
                  );
               }
            } else {
               return response()->json(
                  [
                     'status' => true,
                     'code' => 'FAILED',
                     'message' => 'Movement Variation Not Found !',
                  ],
                  200
               );
            }
         }


         foreach ($validatedData['variants'] as $variant) {

            if ($variant['quantity'] <= $existingVariantMovement->quantity) {
               $calcule = $existingVariantMovement->quantity - $variant['quantity'];

               $existingVariantMovement->quantity = $variant['quantity'];
               $existingVariantMovement->save();


               $existingVariant->quantity = $existingVariant->quantity +  $calcule;
               $existingVariant->save();
            } else {
               $calcule =  $variant['quantity'] -  $existingVariantMovement->quantity;

               $existingVariantMovement->quantity = $variant['quantity'];
               $existingVariantMovement->save();


               $existingVariant->quantity = $existingVariant->quantity - $calcule;
               $existingVariant->save();
            }
         }




         return response()->json(
            [
               'status' => true,
               'code' => 'SUCCESS',
               'message' => 'Inventory Updated Successfully !',
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
                  'message' => 'You Dont Have Access To Inventory Movement',
               ],
               405
            );
         }

         $inventoryMovement = InventoryMovement::find($id);
         if ($inventoryMovement) {
            $inventoryMovementVariations = InventoryMovementVariation::where('inventory_movement_id', $inventoryMovement->id)->get();
            $inventoryState = InventoryState::where('product_id', $inventoryMovement->product_id)->first();

            foreach ($inventoryMovementVariations as $variant) {
               $inventoryStateVariations = InventoryStateVariation::where([
                  'inventory_state_id' => $inventoryState->id,
                  'size' => $variant['size'],
                  'color' => $variant['color']
               ])->first();

               $inventoryStateVariations->quantity += $variant['quantity'];
               $inventoryStateVariations->save();
            }
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
    * Update Recived Inventory Movement.
    *
    * @return \Illuminate\Http\Response
    */
   public function updateReceivedInventoryMovement(Request $request, $id)
   {

      try {

         if (!$request->user()->can('confirmation_inventory_movement')) {
            return response()->json(
               [
                  'status' => false,
                  'code' => 'NOT_ALLOWED',
                  'message' => 'You Dont Have Access To See Product',
               ],
               405
            );
         }
         $inventoryDeliveryStatus = InventoryMovement::where('id', $id)->get()->first();



         if ($inventoryDeliveryStatus) {
            $inventoryDeliveryStatus->is_received = $request->is_received;
            $inventoryDeliveryStatus->save();
            return response()->json([
               'status' => true,
               'code' => 'SUCCESS',
               'message' => 'Status Updated Successfully !'
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
    * Update Note Inventory Movement.
    *
    * @return \Illuminate\Http\Response
    */
   public function updateNoteInventoryMovement(Request $request, $id)
   {

      try {
         if (!$request->user()->can('confirmation_inventory_movement')) {
            return response()->json(
               [
                  'status' => false,
                  'code' => 'NOT_ALLOWED',
                  'message' => 'You Dont Have Access To See Product',
               ],
               405
            );
         }

         $inventoryDeliveryNote = InventoryMovement::where('id', $id)->get()->first();



         if ($inventoryDeliveryNote) {
            $inventoryDeliveryNote->note = $request->note;
            $inventoryDeliveryNote->save();
            return response()->json([
               'status' => true,
               'code' => 'SUCCESS',
               'message' => 'Note Updated Successfully !'
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
    * Update Note and Recieved Inventory Movement.
    *
    * @return \Illuminate\Http\Response
    */
   public function updateReceivedNoteInventoryMovement(Request $request, $id)
   {
      try {
         if (!$request->user()->can('confirmation_inventory_movement')) {
            return response()->json(
               [
                  'status' => false,
                  'code' => 'NOT_ALLOWED',
                  'message' => 'You Dont Have Access To See Product',
               ],
               405
            );
         }

         $inventoryDelivery = InventoryMovement::where('id', $id)->get()->first();



         if ($inventoryDelivery) {
            $inventoryDelivery->note = $request->note;
            $inventoryDelivery->is_received = $request->is_received;

            $inventoryDelivery->save();
            return response()->json([
               'status' => true,
               'code' => 'SUCCESS',
               'message' => 'Note Updated Successfully !'
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
