<?php

namespace App\Services;

use App\Models\Factorisation;
use App\Models\OrderHistory;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Exception;

class NewFactorisationService
{

    public static function observe(&$order)
    {
        $oldAttributes = $order->getOriginal();
        $newAttributes = $order->getAttributes();

        $activeSellerInvoice = self::getActiveSellerInvoice($order->user_id);
        $activeDeliveryInvoice = self::getActiveDeliveryInvoice($order->affectation);

        $oldDelivery = data_get($oldAttributes, 'delivery', 'Select');
        $newDelivery = data_get($newAttributes, 'delivery', 'Select');

        $newIsDelivered = data_get($newAttributes, 'is_delivered', false);

        $oldIsPaidByDelivery = data_get($oldAttributes, 'is_paid_by_delivery', false);
        $newIsPaidByDelivery = data_get($newAttributes, 'is_paid_by_delivery', false);

        $confirmation = data_get($oldAttributes, 'confirmation', 'New');

        if($newDelivery == 'cleared') {
            return;
        }

        if (
            $confirmation == 'confirmer' 
            &&  $newIsPaidByDelivery 
            && !$oldIsPaidByDelivery
            && $newIsDelivered
        ) {

            if ($activeDeliveryInvoice && !$order->factorisation_id) {
                $order->factorisation_id = $activeDeliveryInvoice->id;
            }

            if ($activeSellerInvoice && !$order->seller_factorisation_id) {
                $order->seller_factorisation_id = $activeSellerInvoice->id;
            }

        }

        // if ($order->factorisation_id) {
        //     if ($newDelivery != 'livrer' && $newDelivery != 'paid') {
        //         $oldFactorisation = Factorisation::find($order->factorisation_id);
        //     }
        // }

        // if ($order->seller_factorisation_id) {
        //     if ($newDelivery != 'livrer' && $newDelivery != 'paid') {
        //         $oldFactorisation = Factorisation::find($order->factorisation_id);
        //     }
        // }

    }


    public static function getActiveSellerInvoice($seller_id)
    {
        $invoice = Factorisation::where('user_id', $seller_id)
                ->where('close', false)
                ->where('paid', false)
                ->first();

        if(!$invoice) {
            $invoice = Factorisation::create([
                'factorisation_id' => 'FCT-SL-' . $seller_id . '-' . time(),
                'type' => 'seller',
                'user_id' => $seller_id,
                'commands_number' => 0,
                'price' => 0,
            ]);
        }

        return $invoice;
    }

    
    public static function getActiveDeliveryInvoice($delivery_id)
    {
        $invoice = Factorisation::where('delivery_id', $delivery_id)
                ->where('close', false)
                ->where('paid', false)
                ->first();

        if(!$invoice) {
            $invoice = Factorisation::create([
                'factorisation_id' => 'FCT-DL-' . $delivery_id . '-' . time(),
                'type' => 'delivery',
                'delivery_id' => $delivery_id,
                'commands_number' => 0,
                'price' => 0,
            ]);
        }

        return $invoice;
    }
}