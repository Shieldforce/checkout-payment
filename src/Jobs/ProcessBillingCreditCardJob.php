<?php

namespace Shieldforce\CheckoutPayment\Jobs;


use Carbon\Carbon;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum;
use Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum;
use Shieldforce\CheckoutPayment\Enums\TypePeopleEnum;
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
    }

    public function creditCard()
    {
        $this->checkout = $this?->step4?->ccpCheckout;
        $mp             = new MercadoPagoService();
        $step2          = $this->checkout?->step2()?->first();
        $step4          = $this->checkout?->step4()?->first();

        $data = [
            "value"             => (float)$this->checkout->total_price ?? null,
            "external_id"       => $this->checkout->id ?? null,
            "payer_email"       => $step2->email ?? null,
            "payer_first_name"  => $step2->first_name ?? null,
            "token_card"        => $step4->card_token ?? null,
            "installments"      => $step4->installments ?? null,
            "payment_method_id" => $step4->payment_method_id ?? null,
        ];

        $return = $mp->gerarPagamentoCartao(
            value: $data["value"],
            description: "Pagamento via Cartão",
            external_id: $data["external_id"],
            payer_email: $data["payer_email"],
            payer_first_name: $data["payer_first_name"],
            token_card: $data["token_card"],
            installments: $data["installments"],
            payment_method_id: $data["payment_method_id"],
        );

        logger($return);

        $this->checkout->update([
            "status" => StatusCheckoutEnum::finalizado->value,
        ]);

        $step4->update([
            "request_credit_card_data"  => json_encode($data),
            "response_credit_card_data" => json_encode($return),
        ]);

        //logger($this->step4->toArray());
        /*$checkout->notify(new CheckoutStatusUpdated(
            status: "processing",
            message: "Estamos processando seu pagamento",
            corporateName: env("APP_NAME") ?? "Empresa",
        ));*/
    }
}
