<?php

namespace App\Observers;

use App\Models\Sourcing;
use App\Traits\TrackHistoryTrait;

class SourcingObserver
{
    use TrackHistoryTrait;

    /**
     * Handle the Sourcing "created" event.
     *
     * @param  \App\Models\Sourcing  $sourcing
     * @return void
     */
    public function created(Sourcing $sourcing)
    {
        //
    }

    /**
     * Handle the Sourcing "updated" event.
     *
     * @param  \App\Models\Sourcing  $sourcing
     * @return void
     */
    public function updated(Sourcing $sourcing)
    {
        $this->track($sourcing);
    }

    /**
     * Handle the Sourcing "deleted" event.
     *
     * @param  \App\Models\Sourcing  $sourcing
     * @return void
     */
    public function deleted(Sourcing $sourcing)
    {
        //
    }

    /**
     * Handle the Sourcing "restored" event.
     *
     * @param  \App\Models\Sourcing  $sourcing
     * @return void
     */
    public function restored(Sourcing $sourcing)
    {
        //
    }

    /**
     * Handle the Sourcing "force deleted" event.
     *
     * @param  \App\Models\Sourcing  $sourcing
     * @return void
     */
    public function forceDeleted(Sourcing $sourcing)
    {
        //
    }
}
