<?php

namespace App\Observers;

use App\Models\SupplyRequest;
use App\Traits\TrackHistoryTrait;

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
        $this->track($supplyRequest);
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
