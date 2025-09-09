<?php

namespace Shieldforce\CheckoutPayment\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Shieldforce\CheckoutPayment\Jobs\AllCheckoutsUpdatesPaymentsJob;

class CheckoutPaymentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Carrega views do plugin
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'checkout-payment');

        // Permite publicação de views
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/checkout-payment'),
        ], 'views');

        // Schedule do job
        if (
            $this->app->runningInConsole() &&
            Schema::hasTable('cpp_gateways') &&
            Schema::hasTable('cpp_checkouts') &&
            Schema::hasTable('cpp_checkout_step_1') &&
            Schema::hasTable('cpp_checkout_step_2') &&
            Schema::hasTable('cpp_checkout_step_3') &&
            Schema::hasTable('cpp_checkout_step_4')
        ) {
            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->job(new AllCheckoutsUpdatesPaymentsJob)->hourly();
            });
        }
    }
}
