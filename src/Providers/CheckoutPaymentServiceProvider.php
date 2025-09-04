<?php

namespace Shieldforce\CheckoutPayment\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Shieldforce\CheckoutPayment\Jobs\AllCheckoutsUpdatesPaymentsJob;

class CheckoutPaymentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->job(new AllCheckoutsUpdatesPaymentsJob())->hourly();
        });
    }
}

