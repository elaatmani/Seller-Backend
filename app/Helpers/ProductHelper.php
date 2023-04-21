<?php

namespace App\Helpers;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Models\InventoryMovementVariation;

class ProductHelper {

    // Product::with_state($id);

    static public function with_state($product) {
        // get all variations
        $product_variations = $product->variations;

        // get all product movements
        // $product_movements = InventoryMovement::where('product_id', $product->id)->get();

        $total_quantity = 0;
        // loop through product variations to calculate the available quantity
        foreach ($product_variations as $variation) {

            // calculate total quantity for a product
            $total_quantity += $variation->quantity;


            // for each variation we get all it's movements
            $variation_movements = InventoryMovementVariation::where('product_variation_id', $variation->id)->get();

            // 50 -> 20
            $quantity = $variation->quantity;
            $hold_quantity = 0; // -> 20

            foreach ($variation_movements as $movement) {
                // 50 - 20 = 30; 30 - 10 = 20;
                $quantity -= $movement->quantity;

                // adding the on hold quantity
                // 0 + 20 = 20;
                $hold_quantity += $movement->inventory_movement->is_received ? 0 : $movement->quantity;
            }

            $variation->available_quantity = $quantity; // 20
            $variation->on_hold = $hold_quantity; // 20
        }

        $product->variations = $product_variations;
        $product->total_quantity = $total_quantity;

        return $product;
    }
}
