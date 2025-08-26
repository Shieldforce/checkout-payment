<?php

namespace Shieldforce\CheckoutPayment\Jobs;


use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Shieldforce\CheckoutPayment\Models\CppCheckoutStep4;

class ProcessBillingCreditCardJob implements ShouldQueue
{
    use Dispatchable, Queueable, Batchable;

    public function __construct(public CppCheckoutStep4 $step4)
    {
        //
    }

    public function handle(): void
    {
        logger($this->step4->toArray());
    }
}
