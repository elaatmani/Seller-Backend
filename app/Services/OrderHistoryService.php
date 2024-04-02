<?php

namespace App\Services;


use App\Models\OrderHistory;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderHistoryService
{

    public static function observe($order) {
        $oldAttributes = $order->getOriginal(); // Old values
        $newAttributes = $order->getAttributes(); // New values

        $oldConfirmation = data_get($oldAttributes, 'confirmation', 'New');
        $newConfirmation = data_get($newAttributes, 'confirmation', 'New');

        $oldAffectation = data_get($oldAttributes, 'affectation');
        $newAffectation = data_get($newAttributes, 'affectation');

        $oldDelivery = data_get($oldAttributes, 'delivery', 'Select');
        $newDelivery = data_get($newAttributes, 'delivery', 'Select');

        $oldReconfirmation = data_get($oldAttributes, 'reconfirmation', 'New');
        $newReconfirmation = data_get($newAttributes, 'reconfirmation', 'New');

        $oldUpsell = data_get($oldAttributes, 'upsell', 'Select');
        $newUpsell = data_get($newAttributes, 'upsell', 'Select');

        $oldAgentId = data_get($oldAttributes, 'agente_id', 'None');
        $newAgentId = data_get($newAttributes, 'agente_id', 'None');


        if ($oldConfirmation != $newConfirmation) {
            OrderHistory::create([
                'order_id' => $order->id,
                'user_id' => request()->user()->id,
                'type' => 'confirmation',
                'historique' => $oldConfirmation . ' -> ' . $newConfirmation,
                'note' => $oldConfirmation . ' -> ' . $newConfirmation
            ]);
        }

        if ($oldAffectation != $newAffectation) {
            $oldAffectation = !$oldAffectation ? 'Select' : User::where('id', $oldAffectation)->first()->fullname;
            $newAffectation = !$newAffectation ? 'Select' : User::where('id', $newAffectation)->first()->fullname;

            OrderHistory::create([
                'order_id' => $order->id,
                'user_id' => request()->user()->id,
                'type' => 'affectation',
                'historique' => $oldAffectation . ' -> ' . $newAffectation,
                'note' => $oldAffectation . ' -> ' . $newAffectation
            ]);
        }


        if ($oldReconfirmation != $newReconfirmation) {
            OrderHistory::create([
                'order_id' => $order->id,
                'user_id' => request()->user()->id,
                'type' => 'reconfirmation',
                'historique' => $newReconfirmation,
                'note' => $oldReconfirmation . ' -> ' . $newReconfirmation
            ]);
        }


        if ($oldDelivery != $newDelivery) {
            OrderHistory::create([
                'order_id' => $order->id,
                'user_id' => request()->user()->id,
                'type' => 'delivery',
                'historique' => $oldDelivery . ' -> ' . $newDelivery,
                'note' => $oldDelivery . ' -> ' . $newDelivery
            ]);

            Log::channel('tracking')->info('Order #' . $order->id. ' Delivery: ' . $oldDelivery . ' => ' . $newDelivery);
        }

        if ($oldUpsell != $oldUpsell) {
            OrderHistory::create([
                'order_id' => $order->id,
                'user_id' => request()->user()->id,
                'type' => 'upsell',
                'historique' => $oldUpsell . ' -> ' . $newUpsell,
                'note' => $oldUpsell . ' -> ' . $newUpsell
            ]);
        }

        if ($oldAgentId != $newAgentId) {
            OrderHistory::create([
                'order_id' => $order->id,
                'user_id' => request()->user()->id,
                'type' => 'responsibility',
                'historique' => $oldAgentId . ' -> ' . $newAgentId,
                'note' => $oldAgentId . ' -> ' . $newAgentId,
            ]);
        }


    }

}
