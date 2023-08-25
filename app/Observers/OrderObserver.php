<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderHistory;
use App\Services\FactorisationService;
use App\Services\OrderHistoryService;
use App\Services\RoadRunner;
use Exception;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function created(Order $order)
    {
        $user = request()->user();

        if($user->hasRole('admin') || $user->hasRole('follow-up')) {
            RoadRunner::insert($order);
        };

    }

    /**
     * Handle the Order "updated" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function updated(Order $order)
    {
        //
    }

    public function updating(Order $order)
    {
        $user = request()->user();
        $oldAttributes = $order->getOriginal(); // Old values
        $newAttributes = $order->getAttributes(); // New values

        if($newAttributes['affectation'] != null && $newAttributes['delivery'] == null) {
            $newAttributes['delivery'] = 'dispatch';
        }

        OrderHistoryService::observe($order);
        if($user->hasRole('admin') || $user->hasRole('follow-up')) {
            // throw new Exception('Error admin');

            RoadRunner::sync($order);
        };
        
        FactorisationService::observe($order);
        // throw new Exception('Error admin');



    }

    /**
     * Handle the Order "deleted" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function deleted(Order $order)
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function restored(Order $order)
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function forceDeleted(Order $order)
    {
        //
    }
}
