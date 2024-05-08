<?php

namespace App\Observers;
use App\Models\ProductVariation;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\OrderItemHistory;
use App\Services\OrderItemHistoryService;
use Illuminate\Support\Facades\Log;

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
        $this->OutOfStock2($orderItem);

    }

    /**
     * Handle the OrderItem "updated" event.
     *
     * @param  \App\Models\OrderItem  $orderItem
     * @return void
     */
    public function updated(OrderItem $orderItem)
    {
        $this->OutOfStock2($orderItem);

    }

    public function updating(OrderItem $orderItem)
    {
       
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
    public function OutOfStock(OrderItem $orderItem)
    {
        $productVariation = ProductVariation::where('id', $orderItem->product_variation_id)
        ->first();

        $quantityToCheck = $productVariation->available_quantity - $orderItem->quantity;
        // Log::info('quantityToCheck :' . $quantityToCheck);
        // Log::info('available_quantity :' . $productVariation->available_quantity);
        // Log::info('Quantité :' . $orderItem->quantity);
        if($quantityToCheck <= $productVariation->stockAlert) {
            $productVariation->is_out_of_stock = true;
            Log::info('access true');

        } else {
            Log::info('access false');
            $productVariation->is_out_of_stock = false;
        }

        $productVariation->save();

        Log::info('Product Variation updated for id: ' . $productVariation->id . ' with is_out_of_stock: ' . $productVariation->is_out_of_stock);

        if ($productVariation->available_quantity <= $productVariation->stockAlert && $productVariation->is_out_of_stock){
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
        }
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
