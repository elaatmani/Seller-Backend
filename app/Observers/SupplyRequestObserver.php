<?php

namespace App\Observers;

use App\Models\ProductVariation;
use App\Models\SupplyRequest;
use App\Traits\TrackHistoryTrait;
use Illuminate\Support\Facades\DB;

class SupplyRequestObserver
{
    use TrackHistoryTrait;

    /**
     * Handle the SupplyRequest "created" event.
     *
     * @param  \App\Models\SupplyRequest  $supplyRequest
     * @return void
     */
    public function created(SupplyRequest $supplyRequest)
    {
        //
    }

    /**
     * Handle the SupplyRequest "updated" event.
     *
     * @param  \App\Models\SupplyRequest  $supplyRequest
     * @return void
     */
    public function updated(SupplyRequest $supplyRequest)
    {
        DB::beginTransaction();
        $this->track($supplyRequest);

        $oldAttributes = $supplyRequest->getOriginal(); // Old values
        $newAttributes = $supplyRequest->getAttributes(); // New values

        if(
            $oldAttributes['status'] != config('status.supply_requests.accepted.value') &&
            $newAttributes['status'] == config('status.supply_requests.accepted.value')
            ) {
                $variation = ProductVariation::where('id', $supplyRequest->product_variation_id)->first();
                $variation->quantity += $newAttributes['quantity'];
                $variation->save();
        }

        if(
            $oldAttributes['status'] == config('status.supply_requests.accepted.value') &&
            $newAttributes['status'] != config('status.supply_requests.accepted.value')
            ) {
                $variation = ProductVariation::where('id', $supplyRequest->product_variation_id)->first();
                $variation->quantity -= $newAttributes['quantity'];
                $variation->save();
        }


        DB::commit();
    }

    /**
     * Handle the SupplyRequest "deleted" event.
     *
     * @param  \App\Models\SupplyRequest  $supplyRequest
     * @return void
     */
    public function deleted(SupplyRequest $supplyRequest)
    {
        //
    }

    /**
     * Handle the SupplyRequest "restored" event.
     *
     * @param  \App\Models\SupplyRequest  $supplyRequest
     * @return void
     */
    public function restored(SupplyRequest $supplyRequest)
    {
        //
    }

    /**
     * Handle the SupplyRequest "force deleted" event.
     *
     * @param  \App\Models\SupplyRequest  $supplyRequest
     * @return void
     */
    public function forceDeleted(SupplyRequest $supplyRequest)
    {
        //
    }
}
