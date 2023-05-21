<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Warehouse;
use Illuminate\Support\Arr;
use App\Models\InventoryMovement;
use \Spatie\Permission\Models\Role;
use App\Models\InventoryMovementVariation;

// [on_hold, available_quantity] has relation with product_variation

// [
// on_hand_quantity, -> total of all movements
// movements_confirmed_quantity, -> total of only confirmed movements
// movements_not_confirmed_quantity, -> total on only not confirmed movements
// left_quantity, -> quantity left with the delivery after calculations
// expidier_quantity, -> only exipdier quantity
// delivery_quantity, -> only delivered quantity
// orders -> total of expidier and delivered quantity
// ]
// has relation with delivery and product_variation



class ProductHelper
{
    const COUNTS_WITH_DELIVERY = ['expidier'];
    const REMOVE_FROM_DELIVERY = ['livrer'];
    const COUNTS_IN_WAREHOUSE = ['annuler', 'refuser'];
    const COUNTS_WITH_NONE = [];

    // Product::with_state($id);

    static public function with_state($product)
    {
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



    static public function with_tracking($product)
    {
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

            $quantity = $variation->quantity;
            $hold_quantity = 0;

            foreach ($variation_movements as $movement) {

                $quantity -= $movement->quantity;

                // adding the on hold quantity
                $hold_quantity += $movement->inventory_movement->is_received ? 0 : $movement->quantity;
            }

            $variation->available_quantity = $quantity; // 20
            $variation->on_hold = $hold_quantity; // 20
        }

        foreach ($deliveries as $delivery) {

            // get movements related to this product and this delivery
            $movements = InventoryMovement::where([['product_id', $product->id], ['delivery_id', $delivery->id]])->with('inventory_movement_variations.product_variation')->get();

            // group all inventory movement variation in a single array
            $vs = collect(Arr::flatten($movements->map(fn ($m) => $m->inventory_movement_variations)));

            // all product original variations
            $variations = $product_variations->map(fn ($i) => clone $i);
            $delivery->name = $delivery->firstname . ' ' . $delivery->lastname;

            // get orders related to this delivery and delivery is done or shipped
            $orders_items = OrderItem::join('orders as o', 'order_items.order_id', 'o.id')
                ->select('order_items.*', 'o.id as order_id', 'o.delivery', 'delivery')
                ->where('affectation', $delivery->id)
                ->where('o.confirmation', 'confirmer')
                ->where('o.counts_from_warehouse', true)
                ->whereIn('delivery', ['expidier', 'livrer'])
                ->get();

            $delivery->product_variations = $variations->map(function ($v) use ($vs, $orders_items) {
                $quantity_used_movements = $vs->where('product_variation_id', $v->id)->sum(fn ($i) => $i->quantity);

                $quantity_used_movements_confirmed = $vs->where('product_variation_id', $v->id)->sum(function ($m) {
                    if ($m->inventory_movement->is_received) {
                        return $m->quantity;
                    }

                    return 0;
                });

                $quantity_used_movements_not_confirmed = $vs->where('product_variation_id', $v->id)->sum(function ($m) {
                    if (!$m->inventory_movement->is_received) {
                        return $m->quantity;
                    }

                    return 0;
                });

                $expidier_quantity = $orders_items->where('product_variation_id', $v->id,)->where('delivery', 'expidier')->sum(fn ($o) => $o->quantity);
                $delivery_quantity = $orders_items->where('product_variation_id', $v->id,)->where('delivery', 'livrer')->sum(fn ($o) => $o->quantity);

                $quantity_used_orders = ($expidier_quantity + $delivery_quantity);

                // total quantity has been in hand
                $v->on_hand_quantity = $quantity_used_movements;

                $v->movements_not_confirmed_quantity = $quantity_used_movements_not_confirmed;
                $v->movements_confirmed_quantity = $quantity_used_movements_confirmed;

                $v->left_quantity = $quantity_used_movements - $quantity_used_orders;

                $v->expidier_quantity = $expidier_quantity;
                $v->delivery_quantity = $delivery_quantity;

                $v->orders = $quantity_used_orders;
                return $v;
            });
        }

        foreach ($warehouses as $warehouse) {

            // clone default variations
            $warehouse_product_variations = $product_variations->map(fn ($p) => clone $p);

            // get variations blenogs to this warehouse
            $warehouse_product_variations = $warehouse_product_variations->where('warehouse_id', $warehouse->id);


            foreach ($warehouse_product_variations as $warehouse_product_variation) {
                // get movements related to this variations
                $movement_variations = InventoryMovementVariation::where('product_variation_id', $warehouse_product_variation->id)->get();

                // get total quantity used in those movements
                $used_quantity_from_movements = $movement_variations->sum(fn ($m) => $m->quantity);

                // removes the used quantity from the initial quantity for the variations
                $warehouse_product_variation->on_hand_quantity = $warehouse_product_variation->quantity - $used_quantity_from_movements;
            }

            $warehouse->product_variations = array_values($warehouse_product_variations->toArray());
        }

        $tracking = [
            'deliveries' => $deliveries,
            'warehouses' => $warehouses
        ];

        $product->variations = $product_variations;
        $product->total_quantity = $total_quantity;
        $product->tracking = $tracking;

        return $product;
    }


    public static function get_delivery_state_by_products($delivery)
    {
        $orders = Order::with('items')->where('affectation', $delivery->id)->get();
        $movements = InventoryMovement::with('inventory_movement_variations.product_variation.product')->where('delivery_id', $delivery->id)->get();

        $product_ids_orders = array_unique(Arr::flatten($orders->map(fn ($p) => $p->items->map(fn ($i) => $i->product_id))));
        $product_ids_movements = array_unique(Arr::flatten($movements->map(fn ($i) => $i->inventory_movement_variations->map(fn ($j) => $j->product_variation->product->id))));
        $product_ids = array_unique([...$product_ids_orders, ...$product_ids_movements]);

        $products = Product::with('variations')->whereIn('id', $product_ids)->get();

        $qts = [];

        foreach ($products as $product) {
            foreach ($product->variations as $variation) {

                // get confirmed movements
                $movements_total_confirmed_quantity = collect(Arr::flatten($movements->filter(fn ($i) => $i->is_received)->map(fn ($i) => $i->inventory_movement_variations->filter(fn ($i) => $i->product_variation_id == $variation->id))))->sum(fn ($i) => $i->quantity);
                $movements_total_not_confirmed_quantity = collect(Arr::flatten($movements->filter(fn ($i) => !$i->is_received)->map(fn ($i) => $i->inventory_movement_variations->filter(fn ($i) => $i->product_variation_id == $variation->id))))->sum(fn ($i) => $i->quantity);

                // when calcul quantity from orders when it counts from warehouse
                $counts_from_warehouse_orders = $orders->filter(fn ($i) => (in_array($i->delivery, self::COUNTS_WITH_DELIVERY) && $i->confirmation == 'confirmer') && $i->counts_from_warehouse);
                $quantity_from_warehouse_orders = collect(Arr::flatten($counts_from_warehouse_orders->map(fn ($i) => $i->items->filter(fn ($i) => $i->product_variation_id == $variation->id))))->sum(fn ($i) => $i->quantity);

                $counts_from_delivery_orders = $orders->filter(fn ($i) => (in_array($i->delivery, self::REMOVE_FROM_DELIVERY) && $i->confirmation == 'confirmer') && !$i->counts_from_warehouse);
                $quantity_delivered_from_orders = collect(Arr::flatten($counts_from_delivery_orders->map(fn ($i) => $i->items->filter(fn ($i) => $i->product_variation_id == $variation->id))))->sum(fn ($i) => $i->quantity);


                $total_delivered_orders = $orders->filter(fn ($i) => (in_array($i->delivery, ['livrer']) && $i->confirmation == 'confirmer'));
                $total_delivered_quantity = collect(Arr::flatten($total_delivered_orders->map(fn ($i) => $i->items->filter(fn ($i) => $i->product_variation_id == $variation->id))))->sum(fn ($i) => $i->quantity);

                $quantity_total = ($movements_total_confirmed_quantity + $quantity_from_warehouse_orders) - $quantity_delivered_from_orders;

                $variation->movements_total_confirmed_quantity = $movements_total_confirmed_quantity;
                $variation->movements_total_not_confirmed_quantity = $movements_total_not_confirmed_quantity;
                $variation->total_delivered_quantity = $total_delivered_quantity;
                $variation->on_hand_quantity = $quantity_total;
            }
        }

        return response()->json(['data' => $products]);
    }


    public static function get_delivery_state_by_product($delivery, $product)
    {
        $orders = Order::with('items')->where('affectation', $delivery->id)->get();
        $movements = InventoryMovement::with('inventory_movement_variations.product_variation.product')->where('delivery_id', $delivery->id)->get();

        $variations = [];


        foreach ($product->variations as $variation) {

            $variation = clone $variation;

            // get confirmed movements
            $movements_total_confirmed_quantity = collect(Arr::flatten($movements->filter(fn ($i) => $i->is_received)->map(fn ($i) => $i->inventory_movement_variations->filter(fn ($i) => $i->product_variation_id == $variation->id))))->sum(fn ($i) => $i->quantity);
            $movements_total_not_confirmed_quantity = collect(Arr::flatten($movements->filter(fn ($i) => !$i->is_received)->map(fn ($i) => $i->inventory_movement_variations->filter(fn ($i) => $i->product_variation_id == $variation->id))))->sum(fn ($i) => $i->quantity);

            // when calcul quantity from orders when it counts from warehouse
            $counts_from_warehouse_orders = $orders->filter(fn ($i) => (in_array($i->delivery, self::COUNTS_WITH_DELIVERY) && $i->confirmation == 'confirmer') && $i->counts_from_warehouse);
            $quantity_from_warehouse_orders = collect(Arr::flatten($counts_from_warehouse_orders->map(fn ($i) => $i->items->filter(fn ($i) => $i->product_variation_id == $variation->id))))->sum(fn ($i) => $i->quantity);

            $counts_from_delivery_orders = $orders->filter(fn ($i) => (in_array($i->delivery, self::REMOVE_FROM_DELIVERY) && $i->confirmation == 'confirmer') && !$i->counts_from_warehouse);
            $quantity_delivered_from_orders = collect(Arr::flatten($counts_from_delivery_orders->map(fn ($i) => $i->items->filter(fn ($i) => $i->product_variation_id == $variation->id))))->sum(fn ($i) => $i->quantity);

            $total_delivered_orders = $orders->filter(fn ($i) => (in_array($i->delivery, ['livrer']) && $i->confirmation == 'confirmer'));
            $total_delivered_quantity = collect(Arr::flatten($total_delivered_orders->map(fn ($i) => $i->items->filter(fn ($i) => $i->product_variation_id == $variation->id))))->sum(fn ($i) => $i->quantity);
            $total_shipped_orders = $orders->filter(fn ($i) => (in_array($i->delivery, ['shipped']) && $i->confirmation == 'confirmer'));
            $total_shipped_quantity = collect(Arr::flatten($total_shipped_orders->map(fn ($i) => $i->items->filter(fn ($i) => $i->product_variation_id == $variation->id))))->sum(fn ($i) => $i->quantity);

            $quantity_total = ($movements_total_confirmed_quantity + $quantity_from_warehouse_orders) - $quantity_delivered_from_orders;

            $variation->movements_total_confirmed_quantity = $movements_total_confirmed_quantity;
            $variation->movements_total_not_confirmed_quantity = $movements_total_not_confirmed_quantity;
            $variation->total_delivered_quantity = $total_delivered_quantity;
            $variation->total_shipped_quantity = $total_shipped_quantity;
            $variation->on_hand_quantity = $quantity_total;

            $variations[] = $variation;
        }

        $delivery->name = $delivery->firstname . ' ' . $delivery->lastname;
        $delivery->product_variations = $variations;

        return $delivery;
    }


    static public function get_warehouse_state($warehouse, $product, $orders = null) {

        if(!$orders) {
            $orders = Order::with('items')->get();
        }
        // $movements = InventoryMovement::with('inventory_movement_variations.product_variation.product')->get();
        $product_variations = $product->variations;

        // clone default variations
        $warehouse_product_variations = $product_variations->map(fn ($p) => clone $p);

        // get variations blenogs to this warehouse
        $warehouse_product_variations = $warehouse_product_variations->where('warehouse_id', $warehouse->id);


        foreach ($warehouse_product_variations as $warehouse_product_variation) {
            // get movements related to this variations
            $movement_variations = InventoryMovementVariation::where('product_variation_id', $warehouse_product_variation->id)->get();

            // get total quantity used in those movements
            $movements_total_confirmed_quantity = $movement_variations->sum(function($m) {
                if($m->inventory_movement->is_received) {
                    return $m->quantity;
                }

                return 0;
            });

            $movements_total_not_confirmed_quantity = $movement_variations->sum(function($m) {
                if(!$m->inventory_movement->is_received) {
                    return $m->quantity;
                }

                return 0;
            });

            $used_quantity_from_warehouse_expidier = collect(
                Arr::flatten(
                    $orders
                    ->filter(fn($o) =>$o->counts_from_warehouse)
                    ->where('delivery', 'expidier')
                    ->map(
                        fn($o) => $o->items
                        ->filter(fn($i) => $i->product_variation_id == $warehouse_product_variation->id
                        ))
                    )
                )
                ->sum(fn ($i) => $i->quantity);

            $used_quantity_from_warehouse_livrer = collect(
                Arr::flatten(
                    $orders
                    ->filter(fn($o) =>$o->counts_from_warehouse)
                    ->where('delivery', 'livrer')
                    ->map(
                        fn($o) => $o->items
                        ->filter(fn($i) => $i->product_variation_id == $warehouse_product_variation->id
                        ))
                    )
                )
                ->sum(fn ($i) => $i->quantity);


                $total_used_quantity = $movements_total_confirmed_quantity + $used_quantity_from_warehouse_expidier + $used_quantity_from_warehouse_livrer;

                $warehouse_product_variation->movements_total_confirmed_quantity = $movements_total_confirmed_quantity;
                $warehouse_product_variation->movements_total_not_confirmed_quantity = $movements_total_not_confirmed_quantity;

                $warehouse_product_variation->total_shipped_quantity = $used_quantity_from_warehouse_expidier;
                $warehouse_product_variation->total_delivered_quantity = $used_quantity_from_warehouse_livrer;

                // removes the used quantity from the initial quantity for the variations
                $warehouse_product_variation->on_hand_quantity = $warehouse_product_variation->quantity - $total_used_quantity;
        }

        $warehouse->product_variations = array_values($warehouse_product_variations->toArray());

        return $warehouse;
    }


    static public function get_state($product) {
        $deliveries = Role::where('name', 'delivery')->first()->users->map(fn($d) => self::get_delivery_state_by_product($d, $product));
        $orders = Order::with('items')->get();
        $warehouses = Warehouse::all()->map(fn($w) => self::get_warehouse_state($w, $product, $orders));

        $tracking = [
            'deliveries' => $deliveries,
            'warehouses' => $warehouses
        ];

        // $product->variations = $product_variations;
        // $product->total_quantity = $total_quantity;
        $product->tracking = $tracking;

        return $product;
    }
}
