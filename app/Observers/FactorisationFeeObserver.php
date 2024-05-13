<?php

namespace App\Observers;

use App\Models\Factorisation;
use App\Models\FactorisationFee;
use App\Traits\TrackHistoryTrait;
use Illuminate\Support\Facades\Log;

class FactorisationFeeObserver
{

    use TrackHistoryTrait;
    /**
     * Handle the FactorisationFee "created" event.
     *
     * @param  \App\Models\FactorisationFee  $factorisationFee
     * @return void
     */
    public function created(FactorisationFee $factorisationFee)
    {
        $custom_fields = [
            [
                'field' => 'event',
                'new_value' => 'created',
                'old_value' => null
            ]
        ];

        $this->track($factorisationFee, table: Factorisation::class, id: $factorisationFee->factorisation_id, custom_fields: $custom_fields, func: function($new_value, $old_value, $field) {
            return [
                'new_value' => $new_value,
                'old_value' => $old_value,
                'field' => 'factorisation_fee:' . $field
            ];
        }, event: 'create');
    }

    /**
     * Handle the FactorisationFee "updated" event.
     *
     * @param  \App\Models\FactorisationFee  $factorisationFee
     * @return void
     */
    public function updated(FactorisationFee $factorisationFee)
    {
        $custom_fields = [
            [
                'field' => 'event',
                'new_value' =>'updated',
                'old_value' => null
            ]
        ];

        $this->track($factorisationFee, table: Factorisation::class, id: $factorisationFee->factorisation_id, custom_fields: $custom_fields, func: function($new_value, $old_value, $field) {
            return [
                'new_value' => $new_value,
                'old_value' => $old_value,
                'field' => 'factorisation_fee:' . $field
            ];
        }, event: 'update');
    }

    /**
     * Handle the FactorisationFee "deleting" event.
     *
     * @param  \App\Models\FactorisationFee  $factorisationFee
     * @return void
     */
    public function deleted(FactorisationFee $factorisationFee)
    {
        try {
            //code...

        $custom_fields = [
            [
                'field' => 'event',
                'new_value' => 'deleted',
                'old_value' => null
            ],
            [
                'field' => 'factorisation_fee:fee',
                'new_value' => $factorisationFee->toArray(),
                'old_value' => null
            ]
        ];

        $this->track($factorisationFee, table: Factorisation::class, id: $factorisationFee->factorisation_id, custom_fields: $custom_fields, func: function($new_value, $old_value, $field) {
            return [
                'new_value' => $new_value,
                'old_value' => $old_value,
                'field' => 'factorisation_fee:' . $field
            ];
        }, event: 'delete');

    } catch (\Throwable $th) {
        Log::channel('tracking')->info(json_encode($th));
    }
    }

    /**
     * Handle the FactorisationFee "restored" event.
     *
     * @param  \App\Models\FactorisationFee  $factorisationFee
     * @return void
     */
    public function restored(FactorisationFee $factorisationFee)
    {
        //
    }

    /**
     * Handle the FactorisationFee "force deleted" event.
     *
     * @param  \App\Models\FactorisationFee  $factorisationFee
     * @return void
     */
    public function forceDeleted(FactorisationFee $factorisationFee)
    {
        //
    }
}
