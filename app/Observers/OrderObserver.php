<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Factorisation;
use App\Models\FactorisationFee;
use App\Models\OrderHistory;
use App\Services\FactorisationService;
use App\Services\OrderHistoryService;
use App\Services\OrderItemHistoryService;
use App\Services\RoadRunnerCODSquad;
use App\Services\RoadRunnerVoldo;
use App\Traits\TrackHistoryTrait;
use Exception;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    use TrackHistoryTrait;
    /**
     * Handle the Order "created" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function created(Order $order)
    {
        $newAttributes = $order->getAttributes(); // New values

        if(data_get($newAttributes, 'confirmation', null) == 'refund') {
            $parentOrder = Order::where('id', $newAttributes['parent_id'])->first();

            if (data_get($newAttributes, 'parent_id', null)) {

                $parentOrder = Order::where('id', \data_get($newAttributes, 'parent_id', null))->first();
    
                if (!$parentOrder) {
                    throw new \Exception('Order with parent id not found', 500);
                }
    
                $existingSellerFactorization = Factorisation::where('user_id', data_get(newAttributes, 'user_id', null))
                    ->where('close', false)
                    ->where('paid', false)
                    ->first();
    
                if ($existingSellerFactorization) {
                    FactorisationFee::create([
                        'factorisation_id' => $existingSellerFactorization->id,
                        'feename' => "Refund For Order: $parentOrder->id",
                        'feeprice' => RoadRunnerCODSquad::getPrice($parentOrder)
                    ]);
                }

                if (!$existingSellerFactorization) {
                    $newFactorization = Factorisation::create([
                        'factorisation_id' => 'FCT-' . date('dmY-His', strtotime($order->delivery_date)) . '-SL',
                        'type' => 'seller',
                        'user_id' => data_get($newAttributes, 'user_id', null),
                        'commands_number' => +1,
                        'price' => RoadRunnerCODSquad::getPrice($order), // should i make it order or newAttributes
                    ]);

                    FactorisationFee::create([
                        'factorisation_id' => $newFactorization->id,
                        'feename' => "Refund For Order: $parentOrder->id",
                        'feeprice' => RoadRunnerCODSquad::getPrice($parentOrder)
                    ]);
                }
            }
        }
        // $user = request()->user();

        // if($user->hasRole('admin') || $user->hasRole('follow-up') || $user->hasRole('agente')) {
        //     switch ($order->affectation) {
        //         // case RoadRunnerVoldo::ROADRUNNER_ID:
        //         //     RoadRunnerVoldo::insert($order);
        //         // break;

        //         case RoadRunnerCODSquad::ROADRUNNER_ID:
        //             RoadRunnerCODSquad::insert($order);
        //         break;

        //         default:
        //             # code...
        //             break;
        //     }
        // };

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
        $custom_fields = [];

        if(data_get($oldAttributes, 'delivery') != data_get($newAttributes, 'delivery') && data_get($newAttributes, 'delivery') == 'in-warehouse') {
            $custom_fields[] = [
                'field' => 'scanned_code',
                'old_value' => null,
                'new_value' => request()->input('scanned', 'Not Exists')
            ];
        }

        if(in_array(data_get($newAttributes, 'delivery'), ['paid', 'cleared'])) {
            $order->is_paid_to_seller = true;
        } else {
            $order->is_paid_to_seller = false;

        }

        if(\data_get($newAttributes, 'affectation', null) != null && \data_get($newAttributes, 'delivery', null) == null) {
            $order->delivery = 'dispatch';
            // throw new Exception('Error admin');
        }

        
        if(\data_get($newAttributes, 'confirmation', null) == 'refund' && \data_get($oldAttributes, 'confirmation', null) != 'refund') {
            $parentOrder = Order::where('id', \data_get($newAttributes, 'parent_id', null))->first();

            if (\data_get($newAttributes, 'parent_id', null)) {

                $parentOrder = Order::where('id', data_get($newAttributes, 'parent_id', null))->first();
    
                if (!$parentOrder) {
                    throw new \Exception('Order with parent id not found', 500);
                }
    
                $existingSellerFactorization = Factorisation::where('user_id', data_get($newAttributes, 'user_id', null))
                    ->where('close', false)
                    ->where('paid', false)
                    ->first();
    
                if ($existingSellerFactorization) {
                    FactorisationFee::create([
                        'factorisation_id' => $existingSellerFactorization->id,
                        'feename' => "Refund For Order: $parentOrder->id",
                        'feeprice' => RoadRunnerCODSquad::getPrice($parentOrder)
                    ]);
                }

                  $newFactorization = Factorisation::create([
                    'factorisation_id' => 'FCT-' . date('dmY-His', strtotime($order->delivery_date)) . '-SL',
                    'type' => 'seller',
                    'user_id' => data_get($newAttributes, 'user_id', null),
                    'commands_number' => +1,
                    'price' => RoadRunnerCODSquad::getPrice($order), // should i make it order or newAttributes
                ]);

                FactorisationFee::create([
                    'factorisation_id' => $newFactorization->id,
                    'feename' => "Refund For Order: $parentOrder->id",
                    'feeprice' => RoadRunnerCODSquad::getPrice($parentOrder)
                ]);
            }
        }

        if(\data_get($newAttributes, 'confirmation', null) != 'refund' && \data_get($oldAttributes, 'confirmation', null) == 'refund') {
            $order->parent_id = null;
            // throw new Exception('Error admin');
        }

        if(data_get($oldAttributes, 'delivery') == 'cleared') {
            $order->delivery = 'cleared';

            if(data_get($oldAttributes, 'delivery') != data_get($newAttributes, 'delivery')) {
                $custom_fields[] = [
                    'field' => 'delivery_after_cleared',
                    'old_value' => 'cleared',
                    'new_value' => data_get($newAttributes, 'delivery')
                ];
            }
        }

        RoadRunnerCODSquad::sync($order);
        OrderHistoryService::observe($order);
        FactorisationService::observe($order);
        $this->track($order, custom_fields: $custom_fields);
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
