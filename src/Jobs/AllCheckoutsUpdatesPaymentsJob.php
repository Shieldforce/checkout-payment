<?php

namespace Shieldforce\CheckoutPayment\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum;
use Shieldforce\CheckoutPayment\Models\CppCheckout;
use Shieldforce\CheckoutPayment\Services\MercadoPago\MercadoPagoService;

class AllCheckoutsUpdatesPaymentsJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use Queueable;

    public MercadoPagoService $mp;

    public function __construct()
    {
        $this->mp = new MercadoPagoService;
    }

    public function handle(): void
    {
        logger('AllCheckoutsUpdatesPaymentsJob' . date('Y-m-d H:i:s'));
        $checkouts = CppCheckout::where('status', StatusCheckoutEnum::pendente->value)->get();
        foreach ($checkouts as $checkout) {
            ProcessCheckoutUpdatePaymentsJob::dispatch($checkout);
        }
    }
}
