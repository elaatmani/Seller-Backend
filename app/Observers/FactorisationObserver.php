<?php

namespace App\Observers;

use App\Models\Factorisation;
use App\Models\Order;
use App\Models\User;
use App\Models\WithdrawalMethod;
use App\Services\RoadRunnerCODSquad;
use App\Traits\TrackHistoryTrait;
use Illuminate\Support\Facades\Log;

class FactorisationObserver
{
    use TrackHistoryTrait;

    /**
     * Handle the Factorisation "creating" event.
     *
     * @param  \App\Models\Factorisation  $factorisation
     * @return void
     */
    public function creating(Factorisation $factorisation)
    {
        try {
            if($factorisation->type == 'seller') {

                // get pereferred method if it exists
                $wm = WithdrawalMethod::where([
                    ['seller_id', $factorisation->user_id],
                    ['is_preferred', 1]
                ])->first();

                // if no method is fetched then we get the first available method
                if (!$wm) {
                    $wm = WithdrawalMethod::where('seller_id', $factorisation->user_id)->first();
                }

                if($wm) {
                    $factorisation->withdrawal_method_id = $wm->id;
                }

            }
        } catch (\Throwable $th) {
            $err = [
                'factorisation' => $factorisation->toArray(),
                'error' => $th->getMessage()
            ];

            Log::channel('tracking')->info(json_encode($err));
        }
    }


    /**
     * Handle the Factorisation "created" event.
     *
     * @param  \App\Models\Factorisation  $factorisation
     * @return void
     */
    public function created(Factorisation $factorisation)
    {
    }

    /**
     * Handle the Factorisation "updated" event.
     *
     * @param  \App\Models\Factorisation  $factorisation
     * @return void
     */
    public function updated(Factorisation $factorisation)
    {
        try {
            //code...

        $oldAttributes = $factorisation->getOriginal(); // Old values
        $newAttributes = $factorisation->getAttributes(); // New values

        $custom_fields = [];


        if((!$oldAttributes['paid'] && $newAttributes['paid']) && $factorisation->type == 'seller') {
            $wm = WithdrawalMethod::where([
                ['id', '=', $factorisation->withdrawal_method_id],
            ])->first();


            if($wm){
                $custom_fields[] = [
                    'field' => 'withdrawal_method_details',
                    'new_value' => $wm->toArray(),
                    'old_value' => null,
                ];
            }

            $custom_fields[] = [
                'field' => 'amount_paid',
                'new_value' => $factorisation->seller_order_price,
                'old_value' => null,
            ];

            $custom_fields[] = [
                'field' => 'total_cod_fees',
                'new_value' => $factorisation->total_cod_fees,
                'old_value' => null,
            ];

            $custom_fields[] = [
                'field' => 'total_shipping_fees',
                'new_value' => $factorisation->total_shipping_fees,
                'old_value' => null,
            ];

            $custom_fields[] = [
                'field' => 'product_cost_fees',
                'new_value' => $factorisation->productCostFees(),
                'old_value' => 0,
            ];

            $custom_fields[] = [
                'field' => 'total_other_fees',
                'new_value' => $factorisation->fees()->sum('feeprice'),
                'old_value' => 0,
            ];

            $custom_fields[] = [
                'field' => 'orders',
                'new_value' => $this->getOrders($factorisation),
                'old_value' => 0,
            ];
        }

        $this->track($factorisation, custom_fields: $custom_fields, event: 'update');

        } catch (\Throwable $th) {
            Log::channel('tracking')->info(json_encode($th));
        }
    }

    /**
     * Handle the Factorisation "deleted" event.
     *
     * @param  \App\Models\Factorisation  $factorisation
     * @return void
     */
    public function deleted(Factorisation $factorisation)
    {
        //
    }

    /**
     * Handle the Factorisation "restored" event.
     *
     * @param  \App\Models\Factorisation  $factorisation
     * @return void
     */
    public function restored(Factorisation $factorisation)
    {
        //
    }

    /**
     * Handle the Factorisation "force deleted" event.
     *
     * @param  \App\Models\Factorisation  $factorisation
     * @return void
     */
    public function forceDeleted(Factorisation $factorisation)
    {
        //
    }


    public function getOrders(Factorisation $factorisation) {
        $orders = $factorisation->seller_orders;

        return $orders->map(fn($o) => ([
            'id' => $o->id,
            'confirmation' => $o->confirmation,
            'delivery' => $o->delivery,
            'delivery_date' => $o->delivery_date,
            'price' => RoadRunnerCODSquad::getPrice($o),
            'upsell' => $o->upsell,
            'is_affiliate' => $o->is_affiliate,

        ]))->toArray();


    }
}
