<?php

namespace App\Observers;

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
            }, event: 'create');
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
        //
    }

    public function updating(OrderItem $orderItem)
    {


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
            }, event: 'update');
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
            }, event: 'delete');
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
}
