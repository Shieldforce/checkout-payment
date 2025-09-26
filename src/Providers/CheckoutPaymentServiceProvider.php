<?php

namespace Shieldforce\CheckoutPayment\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Shieldforce\CheckoutPayment\CheckoutPaymentServiceProvider as BaseProvider;
use Shieldforce\CheckoutPayment\Jobs\AllCheckoutsUpdatesPaymentsJob;

class CheckoutPaymentServiceProvider extends BaseProvider
{
    public function boot(): void
    {
        parent::boot(); // carrega Spatie package tools

        // Carrega views do plugin
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'checkout-payment');

        // Permite publicação de views
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/checkout-payment'),
        ], 'views');

        // Schedule do job
        try {
            if (
                $this->app->runningInConsole() &&
                DB::connection()->getPdo() &&
                Schema::connection(config('database.default'))->hasTable('cpp_gateways') &&
                Schema::connection(config('database.default'))->hasTable('cpp_checkouts') &&
                Schema::connection(config('database.default'))->hasTable('cpp_checkout_step_1') &&
                Schema::connection(config('database.default'))->hasTable('cpp_checkout_step_2') &&
                Schema::connection(config('database.default'))->hasTable('cpp_checkout_step_3') &&
                Schema::connection(config('database.default'))->hasTable('cpp_checkout_step_4')
            ) {
                $this->app->booted(function () {
                    $schedule = $this->app->make(Schedule::class);
                    $schedule->job(new AllCheckoutsUpdatesPaymentsJob)->hourly();
                });
            }
        } catch (\Exception $e) {
        }
    }
}
