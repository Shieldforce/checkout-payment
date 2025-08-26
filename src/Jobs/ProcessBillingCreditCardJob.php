<?php

namespace Shieldforce\CheckoutPayment\Jobs;


use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum;
use Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum;
use Shieldforce\CheckoutPayment\Errors\ProcessBillingCreditCardJobException;
use Shieldforce\CheckoutPayment\Models\CppCheckoutStep4;
use Shieldforce\CheckoutPayment\Notifications\CheckoutStatusUpdated;
use Shieldforce\CheckoutPayment\Services\MercadoPago\MercadoPagoService;

class ProcessBillingCreditCardJob implements ShouldQueue
{
    use Dispatchable, Queueable, Batchable;

    public MercadoPagoService $mp;
    public                    $sum = 0;

    public function __construct(public CppCheckoutStep4 $step4)
    {
        $this->mp = new MercadoPagoService();
    }

    public function handle(): void
    {
        $this->checkout = $this?->step4?->ccpCheckout;
        $step1          = $this->checkout?->step1()?->first();

        if (isset($step1->id) && isset($step1->items)) {
            $items = json_decode($step1->items, true);
            foreach ($items as $item) {
                $this->sum += $item['price'] * $item['quantity'];
            }
        }

        if (!isset($this->sum) && $this->sum == 0) {
            throw new ProcessBillingCreditCardJobException(
                "Não existem itens a serem cobrados ou o preço final não foi gerado"
            );
        }

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
