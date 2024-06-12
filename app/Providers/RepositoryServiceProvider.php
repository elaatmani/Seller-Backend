<?php

namespace App\Providers;

use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\AdsRepositoryInterface;
use App\Repositories\Interfaces\FactorisationRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\SupplyRequestRepositoryInterface;
use App\Repositories\Interfaces\SourcingRepositoryInterface;
use App\Repositories\Interfaces\AffiliateRepositoryInterface;
use App\Repositories\FactorisationRepository;
use App\Repositories\OrderRepository;
use App\Repositories\AdsRepository;
use App\Repositories\ProductRepository;
use App\Repositories\SupplyRequestRepository;
use App\Repositories\SourcingRepository;
use App\Repositories\AffiliateRepository;
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
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(SupplyRequestRepositoryInterface::class, SupplyRequestRepository::class);
        $this->app->bind(SourcingRepositoryInterface::class, SourcingRepository::class);
        $this->app->bind(AffiliateRepositoryInterface::class, AffiliateRepository::class);
    }
}
