<?php

namespace App\Providers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Policies\SalePolicy;
use App\Policies\PurchasePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Services\ReturnService;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
         $this->app->singleton(ReturnService::class, function ($app) {
            return new ReturnService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //

        Gate::policy(Sale::class, SalePolicy::class);
        Gate::policy(Purchase::class, PurchasePolicy::class);
    }
}
