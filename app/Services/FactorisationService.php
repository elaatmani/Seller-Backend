<?php

namespace App\Services;

use App\Models\Factorisation;
use App\Models\OrderHistory;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Exception;

class FactorisationService
{

    public static function observe(&$order)
    {
        // throw new Exception('not working');

        try {
            $oldAttributes = $order->getOriginal(); // Old values
            $newAttributes = $order->getAttributes(); // New values

            $oldConfirmation = data_get($oldAttributes, 'confirmation', 'New');
            // $newConfirmation = data_get($newAttributes, 'confirmation', 'New');

            $oldDelivery = data_get($oldAttributes, 'delivery', 'Select');
            $newDelivery = data_get($newAttributes, 'delivery', 'Select');

            if($newDelivery == 'cleared') {
                return;
            }

  
            if ($oldConfirmation == 'confirmer' &&  $newDelivery == 'livrer' && ($newDelivery != $oldDelivery)) {
                $order->cmd = 'CMD-' . date('dmY-His', strtotime($order->created_at));
                $order->delivery_date = now();
                
                $existingDeliveryFactorization = Factorisation::where('delivery_id', $order->affectation)
                ->where('close', false)
                ->where('paid', false)
                ->first();
                
                $existingSellerFactorization = Factorisation::where('user_id', $order->user_id)
                ->where('close', false)
                ->where('paid', false)
                ->first();
                
                if ($existingDeliveryFactorization && !$order->factorisation_id) {
                    // Update the existing factorization
                    $existingDeliveryFactorization->price += RoadRunnerService::getPrice($order);
                    $existingDeliveryFactorization->commands_number += 1;
                    $existingDeliveryFactorization->save();
                    
                    $order->factorisation_id = $existingDeliveryFactorization->id;
                }
                if ($existingSellerFactorization && !$order->seller_factorisation_id) {
                    
                    $existingSellerFactorization->price += RoadRunnerService::getPrice($order);
                    $existingSellerFactorization->commands_number += 1;
                    $existingSellerFactorization->save();
                    
                    $order->seller_factorisation_id = $existingSellerFactorization->id;
                }
                if (!$existingDeliveryFactorization && !$order->factorisation_id) {
                    // Create a new factorization
                    $newFactorization = Factorisation::create([
                        'factorisation_id' => 'FCT-' . date('dmY-His', strtotime($order->delivery_date)) . '-DL',
                        'type' => 'delivery',
                        'delivery_id' => $order->affectation,
                        'commands_number' => +1,
                        'price' => RoadRunnerService::getPrice($order),
                    ]);
                    $order->factorisation_id = $newFactorization->id;

                }
                if (!$existingSellerFactorization && !$order->seller_factorisation_id) {
                    
                    $newSellerFactorization = Factorisation::create([
                        'factorisation_id' => 'FCT-' . date('dmY-His', strtotime($order->delivery_date)) . '-SL',
                        'type' => 'seller',
                        'user_id' => $order->user_id,
                        'commands_number' => +1,
                        'price' => RoadRunnerService::getPrice($order),
                    ]);

                    $order->seller_factorisation_id = $newSellerFactorization->id;
                    
                }
            }


            if ($order->factorisation_id) {
                if ($newDelivery != 'livrer' && $newDelivery != 'paid') { // livrer !in_array($newDelivery, ['livrer', 'paid']) delivered
                    $oldFactorisation = Factorisation::find($order->factorisation_id);
                   if($oldFactorisation->close || $oldFactorisation->paid) return;
                    $order->delivery_date = null;
                    if ($oldFactorisation) {
                        $oldFactorisation->price -= RoadRunnerService::getPrice($order);
                        $oldFactorisation->commands_number -= 1;
                        $oldFactorisation->save();
                        if ($oldFactorisation->delivery_orders()->count() == 1) {
                            $oldFactorisation->delete();
                        }
                    }
                    $order->factorisation_id = null;
                };
            };


            if ($order->seller_factorisation_id) {
                if ($newDelivery != 'livrer' && $newDelivery != 'paid') { // livrer !in_array($newDelivery, ['livrer', 'paid']) delivered
                    $oldSellerFactorisation = Factorisation::find($order->seller_factorisation_id);
                    if($oldSellerFactorisation->close || $oldSellerFactorisation->paid) return;
                    $order->delivery_date = null;
                    if ($oldSellerFactorisation) {
                        $oldSellerFactorisation->price -= RoadRunnerService::getPrice($order);
                        $oldSellerFactorisation->commands_number -= 1;
                        $oldSellerFactorisation->save();

                        if ($oldSellerFactorisation->seller_orders()->count() == 1) {
                            $oldSellerFactorisation->delete();
                        }
                    }
                    $order->seller_factorisation_id = null;
                };
            };
        } catch (\Throwable $th) {
            throw new Exception($th);
        };
    }
}
