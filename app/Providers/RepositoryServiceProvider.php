<?php

namespace App\Providers;

use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\OrderRepository;
use App\Repositories\Interfaces\AdsRepositoryInterface;
use App\Repositories\AdsRepository;
use App\Repositories\FactorisationRepository;
use App\Repositories\Interfaces\FactorisationRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(AdsRepositoryInterface::class, AdsRepository::class);
        $this->app->bind(FactorisationRepositoryInterface::class, FactorisationRepository::class);
    }
}
