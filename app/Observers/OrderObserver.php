<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderHistory;
use App\Services\OrderHistoryService;
use App\Services\RoadRunnerCODSquad;
use App\Services\RoadRunnerVoldo;
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
            switch ($order->affectation) {
                case RoadRunnerVoldo::ROADRUNNER_ID:
                    RoadRunnerVoldo::insert($order);
                break;

                case RoadRunnerCODSquad::ROADRUNNER_ID:
                    RoadRunnerCODSquad::insert($order);
                break;

                default:
                    # code...
                    break;
            }
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
            $order->delivery = 'dispatch';
            // throw new Exception('Error admin');
        }

        if($newAttributes['delivery'] == 'annuler') {
            $order->followup_id = 19;
        }

        OrderHistoryService::observe($order);
        if($user->hasRole('admin') || $user->hasRole('follow-up')) {
            // throw new Exception('Error admin');
            RoadRunnerCODSquad::sync($order);
            RoadRunnerVoldo::sync($order);
        };


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
