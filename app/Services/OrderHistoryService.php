<?php

namespace App\Services;


use App\Models\OrderHistory;
use Illuminate\Support\Facades\Http;

class OrderHistoryService
{

    public static function observe($order) {
        $oldAttributes = $order->getOriginal(); // Old values
        $newAttributes = $order->getAttributes(); // New values

        if ($oldAttributes['confirmation'] != $newAttributes['confirmation']) {

            $oldConfirmation = !$oldAttributes['confirmation'] ? 'New' : $oldAttributes['confirmation'];
            $newConfirmation = !$newAttributes['confirmation'] ? 'New' : $newAttributes['confirmation'];

            OrderHistory::create([
                'order_id' => $order->id,
                'user_id' => request()->user()->id,
                'type' => 'confirmation',
                'historique' => $newAttributes['confirmation'],
                'note' => $oldConfirmation . ' -> ' . $newConfirmation
            ]);
        }

        if ($oldAttributes['followup_confirmation'] != $newAttributes['followup_confirmation']) {
            $oldFollowupConfirmation = !$oldAttributes['followup_confirmation'] ? 'New' : $oldAttributes['followup_confirmation'];
            $newFollowupConfirmation = !$newAttributes['followup_confirmation'] ? 'New' : $newAttributes['followup_confirmation'];

            OrderHistory::create([
                'order_id' => $order->id,
                'user_id' => request()->user()->id,
                'type' => 'reconfirmation',
                'historique' => $newAttributes['followup_confirmation'],
                'note' => $oldFollowupConfirmation . ' -> ' . $newFollowupConfirmation
            ]);
        }

        if ($oldAttributes['delivery'] != $newAttributes['delivery']) {
            $oldDelivery = !$oldAttributes['delivery'] ? 'Select' : $oldAttributes['delivery'];
            $newDelivery = !$newAttributes['delivery'] ? 'Select' : $newAttributes['delivery'];

            OrderHistory::create([
                'order_id' => $order->id,
                'user_id' => request()->user()->id,
                'type' => 'delivery',
                'historique' => $newAttributes['delivery'],
                'note' => $oldDelivery . ' -> ' . $newDelivery
            ]);
        }

        if ($oldAttributes['upsell'] != $newAttributes['upsell']) {
            $oldUpsell = !$oldAttributes['upsell'] ? 'Select' : $oldAttributes['upsell'];
            $newUpsell = !$newAttributes['upsell'] ? 'Select' : $newAttributes['upsell'];

            OrderHistory::create([
                'order_id' => $order->id,
                'user_id' => request()->user()->id,
                'type' => 'upsell',
                'historique' => $newAttributes['upsell'],
                'note' => $oldUpsell . ' -> ' . $newUpsell
            ]);
        }

        if ($oldAttributes['agente_id'] != $newAttributes['agente_id']) {
            OrderHistory::create([
                'order_id' => $order->id,
                'user_id' => request()->user()->id,
                'type' => 'responsibility',
                'historique' => $newAttributes['agente_id'],
                'note' => $oldAttributes['agente_id'] . ' -> ' . $newAttributes['agente_id'],
            ]);
        }
    }

}
