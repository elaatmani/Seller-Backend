<?php

namespace App\Observers;
use App\Models\ProductVariation;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Exception;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemHistory;
use App\Traits\TrackHistoryTrait;

use Illuminate\Support\Facades\Log;
use App\Services\OrderItemHistoryService;

class OrderItemObserver
{
    use TrackHistoryTrait;
    /**
     * Handle the OrderItem "created" event.
     *
     * @param  \App\Models\OrderItem  $orderItem
     * @return void
     */
    public function created(OrderItem $orderItem)
    {
        try {
            $this->OutOfStock2($orderItem);
        } catch (\Throwable $th) {
            Log::channel('tracking')->info(json_encode($th->getMessage()));
        }

        try {
            $custom_fields = [
                [
                    'field' => 'event',
                    'new_value' => 'created',
                    'old_value' => null
                ]
            ];

            $this->track($orderItem, table: Order::class, id: $orderItem->order_id, custom_fields: $custom_fields, func: function($new_value, $old_value, $field) {
                return [
                    'new_value' => $new_value,
                    'old_value' => $old_value,
                    'field' => 'order_item:' . $field
                ];
            });
        } catch (\Throwable $th) {
            $err = [
                'order_item' => $orderItem->toArray(),
                'error' => $th->getMessage()
            ];

            Log::channel('tracking')->info(json_encode($err));
        }
    }

    /**
     * Handle the OrderItem "updated" event.
     *
     * @param  \App\Models\OrderItem  $orderItem
     * @return void
     */
    public function updated(OrderItem $orderItem)
    {
        try {
            $this->OutOfStock2($orderItem);
        } catch (\Throwable $th) {
            Log::channel('tracking')->info(json_encode($th->getMessage()));
        }

    }

    public function updating(OrderItem $orderItem)
    {
       
        OrderItemHistoryService::observe($orderItem);


        try {
            $custom_fields = [
                [
                    'field' => 'event',
                    'new_value' => 'updated',
                    'old_value' => null
                ]
            ];

            $this->track($orderItem, table: Order::class, id: $orderItem->order_id, custom_fields: $custom_fields, func: function($new_value, $old_value, $field) {
                return [
                    'new_value' => $new_value,
                    'old_value' => $old_value,
                    'field' => 'order_item:' . $field
                ];
            });
        } catch (\Throwable $th) {
            $err = [
                'order_item' => $orderItem->toArray(),
                'error' => $th->getMessage()
            ];

            Log::channel('tracking')->info(json_encode($err));
        }

        OrderItemHistoryService::observe($orderItem);

        // throw new Exception($orderItem);



    }

    /**
     * Handle the OrderItem "deleted" event.
     *
     * @param  \App\Models\OrderItem  $orderItem
     * @return void
     */
    public function deleted(OrderItem $orderItem)
    {
        try {
            $custom_fields = [
                [
                    'field' => 'event',
                    'new_value' => 'deleted',
                    'old_value' => null
                ]
            ];

            $this->track($orderItem, table: Order::class, id: $orderItem->order_id, custom_fields: $custom_fields, func: function($new_value, $old_value, $field) {
                return [
                    'new_value' => $new_value,
                    'old_value' => $old_value,
                    'field' => 'order_item:' . $field
                ];
            });
        } catch (\Throwable $th) {
            $err = [
                'order_item' => $orderItem->toArray(),
                'error' => $th->getMessage()
            ];

            Log::channel('tracking')->info(json_encode($err));
        }
    }

    /**
     * Handle the OrderItem "restored" event.
     *
     * @param  \App\Models\OrderItem  $orderItem
     * @return void
     */
    public function restored(OrderItem $orderItem)
    {
        //
    }

    /**
     * Handle the OrderItem "force deleted" event.
     *
     * @param  \App\Models\OrderItem  $orderItem
     * @return void
     */
    public function forceDeleted(OrderItem $orderItem)
    {
        //
    }

    public function OutOfStock2(OrderItem $orderItem)
    {

        $productVariation = ProductVariation::where('id', $orderItem->product_variation_id)
        ->first();
        $oldAttributes = $orderItem->getOriginal(); // Old values
        $newAttributes = $orderItem->getAttributes(); // New values

        $diff = data_get($newAttributes, 'quantity', 0) - data_get($oldAttributes, 'quantity', 0);

        $quantityToCheck = $productVariation->available_quantity - $diff;

        // Log::info('quantityToCheck :' . $quantityToCheck);
        // Log::info('available_quantity :' . $productVariation->available_quantity);
        // Log::info('old Quantité :' . $oldAttributes['quantity']);
        // Log::info('new Quantité :' . $newAttributes['quantity']);
        // Log::info('diff :' . $diff);

        
        Log::info('Product Variation updated for id: ' . $productVariation->id . ' with is_out_of_stock: ' . $productVariation->is_out_of_stock);

        if ($quantityToCheck <= $productVariation->stockAlert){
            if(!$productVariation->is_out_of_stock) {
                $adminRole = Role::where('name', 'admin')->first();
                $message = 'Product ' . $productVariation->product->name . ' is running low on stock.';
                $action = $orderItem->id;
                $opt = ['type' => 'products', 'target' => $productVariation->product->id];
                foreach ($adminRole->users as $admin) {
                    toggle_notification($admin->id, $message, $action,$opt);
                    // Log::info('admin');
                }
                $seller = User::find($productVariation->product->user_id);
                if ($seller) {
                    toggle_notification($seller->id, $message, $action,$opt);
                }
                
                $productVariation->is_out_of_stock = true;
            }
        } else {
            $productVariation->is_out_of_stock = false;
        }
        $productVariation->save();
    }
}
