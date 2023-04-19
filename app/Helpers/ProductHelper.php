<?php

namespace App\Helpers;
use App\Models\Product;
use App\Models\InventoryMovement;

class ProductHelper {

    // Product::with_state($id);

    static public function with_state($product) {
        // calculate_quantity($id)

        // find the product
        // $product = Product::find($id); // id: 1

        // get all variations
        $product_variations = $product->variations;
        // [
        //    { id:1, size: 'M', color: 'red', quantity: 50 },
        //    { id:2, size: 'M', color: 'blue', quantity: 40 } #
        // ]
        // Quantity Total: 90

        // get all product movements
        $product_movements = InventoryMovement::where('product_id', $product->id)->get();
        // [
        //    {id: 1, product_id: 1, product_variation_id: 1, quantity: 20, is_received: false, delivery: 1},
        //    {id: 2, product_id: 1, product_variation_id: 2, quantity: 30, is_received: true, delivery: 1}, #
        //    {id: 1, product_id: 1, product_variation_id: 1, quantity: 10, is_received: true, delivery: 2},
        //    {id: 2, product_id: 1, product_variation_id: 2, quantity: 10, is_received: true, delivery: 2} #
        // ]
        $total_quantity = 0;
        // loop through product variations to calculate the available quantity
        foreach ($product_variations as $variation) {

            // calculate total quantity for a product
            $total_quantity += $variation->quantity;

            // for each variation we get all it's movements
            $variation_movements = $product_movements->where('product_variation_id', $variation->id)->all();

            // 50 -> 20
            $quantity = $variation->quantity;
            $hold_quantity = 0; // -> 20

            foreach ($variation_movements as $movement) {
                // 50 - 20 = 30; 30 - 10 = 20;
                $quantity -= $movement->quantity;

                // adding the on hold quantity
                // 0 + 20 = 20;
                $hold_quantity += $movement->is_received ? 0 : $movement->quantity;
            }

            $variation->available_quantity = $quantity; // 20
            $variation->on_hold = $hold_quantity; // 20
        }

        $product->variations = $product_variations;
        $product->total_quantity = $total_quantity;

        return $product;
    }
}
