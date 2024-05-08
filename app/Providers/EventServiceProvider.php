<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Sourcing;
use App\Models\User;
use App\Models\SupplyRequest;
use App\Models\Factorisation;
use App\Models\FactorisationFee;
use App\Observers\OrderObserver;
use App\Observers\OrderItemObserver;
use App\Observers\SourcingObserver;
use App\Observers\SupplyRequestObserver;
use App\Observers\FactorisationFeeObserver;
use App\Observers\FactorisationObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Order::observe(OrderObserver::class);
        OrderItem::observe(OrderItemObserver::class);
        SupplyRequest::observe(SupplyRequestObserver::class);
        Sourcing::observe(SourcingObserver::class);
        User::observe(UserObserver::class);
        Factorisation::observe(FactorisationObserver::class);
        FactorisationFee::observe(FactorisationFeeObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
