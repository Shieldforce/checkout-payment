<?php

namespace Shieldforce\CheckoutPayment\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Shieldforce\CheckoutPayment\Jobs\AllCheckoutsUpdatesPaymentsJob;

class CheckoutPaymentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Carrega views do plugin
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'checkout-payment');

        // Permite publicação de views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/checkout-payment'),
        ], 'views');

        // Schedule do job
        if ($this->app->runningInConsole()) {
            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->job(new AllCheckoutsUpdatesPaymentsJob())->hourly();
            });
        }
    }
}

