<?php

namespace App\Helpers;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Models\InventoryMovementVariation;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Arr;
use \Spatie\Permission\Models\Role;


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



    static public function with_tracking($product) {
        // get all variations
        $product_variations = $product->variations;
        $deliveries = Role::where('name', 'delivery')->first()->users;
        $warehouses = Warehouse::all();

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

        foreach ($deliveries as $delivery) {
            $movements = InventoryMovement::where([['product_id', $product->id], ['delivery_id', $delivery->id]])->with('inventory_movement_variations.product_variation')->get();

            $vs = collect(Arr::flatten($movements->map(fn($m) => $m->inventory_movement_variations)));

            $variations = $product_variations->map(fn($i) => clone $i);
            $delivery->name = $delivery->firstname . ' ' . $delivery->lastname;

            $delivery->product_variations = $variations->map(function ($v) use($vs) {
                $q = $vs->where('product_variation_id', $v->id)->sum(fn($i) => $i->quantity);
                $v->on_hand_quantity = $q;
                return $v;
            });
        }

        foreach($warehouses as $warehouse) {

            // clone default variations
            $warehouse_product_variations = $product_variations->map(fn($p) => clone $p);

            // get variations blenogs to this warehouse
            $warehouse_product_variations = $warehouse_product_variations->where('warehouse_id', $warehouse->id);


            foreach($warehouse_product_variations as $warehouse_product_variation) {
                // get movements related to this variations
                $movement_variations = InventoryMovementVariation::where('product_variation_id', $warehouse_product_variation->id)->get();

                // get total quantity used in those movements
                $used_quantity = $movement_variations->sum(fn($m) => $m->quantity);

                // removes the used quantity from the initial quantity for the variations
                $warehouse_product_variation->on_hand_quantity = $warehouse_product_variation->quantity - $used_quantity;
            }

            $warehouse->product_variations = $warehouse_product_variations;

        }

        $tracking = [
            'deliveries' => $deliveries,
            'warehouses' => $warehouses
        ];

        $product->variations = $product_variations;
        $product->total_quantity = $total_quantity;
        $product->total_quantity = $total_quantity;
        $product->tracking = $tracking;

        return $product;
    }
}
