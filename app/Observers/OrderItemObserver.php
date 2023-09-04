<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemHistory;

use App\Services\OrderItemHistoryService;

use Exception;

class OrderItemObserver
{
    /**
     * Handle the OrderItem "created" event.
     *
     * @param  \App\Models\OrderItem  $orderItem
     * @return void
     */
    public function created(OrderItem $orderItem)
    {
        //
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

            $order = Order::find($orderItem->order_id);
         
            $totalPrice = $order->items->sum(function ($item) {
                return $item->price;
            });

            // Update the total price in the order
            $order->price = $totalPrice;
            $order->save();

  
        
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
        //
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
