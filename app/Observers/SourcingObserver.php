<?php

namespace App\Observers;

use App\Models\Sourcing;
use Spatie\Permission\Models\Role;
use App\Traits\TrackHistoryTrait;
use Illuminate\Support\Facades\Log;

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
        $adminRole = Role::where('name', 'admin')->first();
        $admins = $adminRole->users()->where('id', '!=', auth()->id())->get();
        $message = auth()->user()->firstname . ' ' . auth()->user()->lastname . " has added new sourcing.";
        $action = $sourcing->id;
        foreach ($admins as $admin) {
            toggle_notification($admin->id,$message,$action);
        }
    }

    /**
     * Handle the Sourcing "updated" event.
     *
     * @param  \App\Models\Sourcing  $sourcing
     * @return void
     */
    public function updated(Sourcing $sourcing)
    {   
        $userRole = auth()->user()->roles->pluck('name')->first();
        if ($userRole == 'seller') {
            $adminRole = Role::where('name', 'admin')->first();
            $admins = $adminRole->users()->where('id', '!=', auth()->id())->get();
            $message = auth()->user()->firstname . ' ' . auth()->user()->lastname . " has confirmed the sourcing for product " . $sourcing->product_name .".";
            $action = $sourcing->id;

            foreach ($admins as $admin) {
                toggle_notification($admin->id, $message,$action);
            }
        } elseif ($userRole == 'admin') {
            if ($sourcing->isDirty('quotation_status')) {
                $mainUserId = $sourcing->user_id;
                $message = auth()->user()->firstname . ' ' . auth()->user()->lastname . " has updated your sourcing for product " . $sourcing->product_name . " with status: " . $sourcing->quotation_status . ".";
                $action = $sourcing->id;

                toggle_notification($mainUserId, $message,$action);
            }
            if ($sourcing->isDirty('sourcing_status')) {
                $mainUserId = $sourcing->user_id;
                $message = auth()->user()->firstname . ' ' . auth()->user()->lastname . " has updated your sourcing for product " . $sourcing->product_name . " with status: " . $sourcing->sourcing_status . ".";
                $action = $sourcing->id;

                toggle_notification($mainUserId, $message,$action);
            }
        }
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
