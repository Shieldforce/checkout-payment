<?php

namespace Shieldforce\CheckoutPayment\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum;
use Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum;
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
        logger("ProcessCheckoutUpdatePaymentsJob, checkout id: {$this->checkout->id} - " . date("Y-m-d H:i:s"));

        $payments = $this->mp->buscarPagamentoPorExternalId($this->checkout->id);

        $paymentsArray = is_array($payments) ? $payments : json_decode($payments, true);

        $updateData = [
            'return_gateway' => $payments,
        ];

        $approvedPayment = collect($paymentsArray)->first(function ($payment) {
            return ($payment['status'] ?? '') === 'approved';
        });

        if ($approvedPayment) {
            $updateData['status']         = StatusCheckoutEnum::finalizado->value;
            $updateData['method_checked'] = $this->methodTransformer($approvedPayment['method']);

            logger("Checkout id: {$this->checkout->id} atualizado para status 5 devido a pagamento aprovado. Método: {$updateData['method_type']}");
        }

        $this->checkout->update($updateData);
    }

    public function methodTransformer($methodMP)
    {
        if($methodMP== "pix") {
            return MethodPaymentEnum::pix->value;
        }

        if($methodMP== "bolbradesco") {
            return MethodPaymentEnum::billet->value;
        }

        return MethodPaymentEnum::credit_card->value;
    }
}
