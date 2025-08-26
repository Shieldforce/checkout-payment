<?php

namespace Shieldforce\CheckoutPayment\Jobs;


use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum;
use Shieldforce\CheckoutPayment\Errors\ProcessBillingCreditCardJobException;
use Shieldforce\CheckoutPayment\Models\CppCheckoutStep4;
use Shieldforce\CheckoutPayment\Notifications\CheckoutStatusUpdated;

class ProcessBillingCreditCardJob implements ShouldQueue
{
    use Dispatchable, Queueable, Batchable;

    public function __construct(public CppCheckoutStep4 $step4)
    {
        //
    }

    public function handle(): void
    {
        $checkout = $this?->step4?->ccpCheckout;
        $step1    = $checkout?->step1()?->first();

        if (isset($step1->id) && isset($step1->items)) {
            $items = json_decode($step1->items, true);
            $sum   = 0;
            foreach ($items as $item) {
                $sum += $item['price'] * $item['quantity'];
            }
        }

        if (!isset($sum) && $sum == 0) {
            throw new ProcessBillingCreditCardJobException(
                "Não existem itens a serem cobrados ou o preço final não foi gerado"
            );
        }

        // colocar dentro do job de pagamento após aprovado ou no job que verifica pagamento ---
        $checkout->update([
            "total_price" => $sum,
            "status"      => StatusCheckoutEnum::finalizado->value,
            "startOnStep" => 5,
        ]);

        //logger($this->step4->toArray());

        /*$checkout->notify(new CheckoutStatusUpdated(
            status: "processing",
            message: "Estamos processando seu pagamento",
            corporateName: env("APP_NAME") ?? "Empresa",
        ));*/
    }
}
