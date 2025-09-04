<?php

namespace Shieldforce\CheckoutPayment\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Shieldforce\CheckoutPayment\Models\CppCheckout;
use Shieldforce\CheckoutPayment\Services\MercadoPago\MercadoPagoService;

class ProcessCheckoutUpdatePaymentsJob implements ShouldQueue
{
    use Dispatchable, Queueable, Batchable;

    public MercadoPagoService $mp;

    public function __construct(public CppCheckout $checkout)
    {
        $this->mp = new MercadoPagoService();
    }

    public function handle(): void
    {
        $payments = $this->mp->buscarPagamentoPorExternalId($this->checkout->id);
        $this->checkout->update([
            "return_gateway" => $payments,
        ]);
    }
}
