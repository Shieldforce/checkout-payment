<?php

namespace Shieldforce\CheckoutPayment\Jobs;


use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum;
use Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum;
use Shieldforce\CheckoutPayment\Errors\ProcessBillingCreditCardJobException;
use Shieldforce\CheckoutPayment\Models\CppCheckout;
use Shieldforce\CheckoutPayment\Models\CppCheckoutStep4;
use Shieldforce\CheckoutPayment\Notifications\CheckoutStatusUpdated;
use Shieldforce\CheckoutPayment\Services\MercadoPago\MercadoPagoService;

class ProcessBillingCreditCardJob implements ShouldQueue
{
    use Dispatchable, Queueable, Batchable;

    public MercadoPagoService $mp;
    public CppCheckout        $checkout;

    public function __construct(public CppCheckoutStep4 $step4)
    {
        $this->mp = new MercadoPagoService();
    }

    public function handle(): void
    {
        $this->checkout = $this?->step4?->ccpCheckout;

        if ($this->checkout->method_checked == MethodPaymentEnum::credit_card->value) {
            $this->creditCard();
        }

        if ($this->checkout->method_checked == MethodPaymentEnum::pix->value) {
            $this->pix();
        }

        if ($this->checkout->method_checked == MethodPaymentEnum::billet->value) {
            $this->billet();
        }
    }

    public function creditCard()
    {
        $this->checkout = $this?->step4?->ccpCheckout;
        //logger($this->step4->toArray());
        /*$checkout->notify(new CheckoutStatusUpdated(
            status: "processing",
            message: "Estamos processando seu pagamento",
            corporateName: env("APP_NAME") ?? "Empresa",
        ));*/
    }

    public function pix()
    {
        $this->checkout = $this?->step4?->ccpCheckout;
        //logger($this->step4->toArray());
        /*$checkout->notify(new CheckoutStatusUpdated(
            status: "processing",
            message: "Estamos processando seu pagamento",
            corporateName: env("APP_NAME") ?? "Empresa",
        ));*/
    }

    public function billet()
    {
        $this->checkout = $this?->step4?->ccpCheckout;
        //logger($this->step4->toArray());
        /*$checkout->notify(new CheckoutStatusUpdated(
            status: "processing",
            message: "Estamos processando seu pagamento",
            corporateName: env("APP_NAME") ?? "Empresa",
        ));*/
    }
}
