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
    use Batchable;
    use Dispatchable;
    use Queueable;

    public MercadoPagoService $mp;

    public function __construct(public CppCheckout $checkout)
    {
        $this->mp = new MercadoPagoService;
    }

    public function handle(): void
    {
        logger("ProcessCheckoutUpdatePaymentsJob, checkout id: {$this->checkout->id} - " . now());

        $payments = $this->mp->buscarPagamentoPorExternalId($this->checkout->id);
        $paymentsArray = is_array($payments) ? $payments : json_decode($payments, true);

        // Ordena pela data do pagamento (ajuste para o campo correto da API)
        $paymentsCollection = collect($paymentsArray)->sortByDesc(
            fn ($p) => $p['date_created'] ?? $p['date_approved'] ?? null
        );

        $updateData = [
            'return_gateway' => $payments,
        ];

        // 1. Procura um aprovado (não importa a ordem, qualquer aprovado já finaliza)
        $approvedPayment = $paymentsCollection->first(
            fn ($p) => ($p['status'] ?? '') === 'approved'
        );

        if ($approvedPayment) {
            $updateData['status'] = StatusCheckoutEnum::finalizado->value;
            $updateData['method_checked'] = $this->methodTransformer($approvedPayment['method'] ?? null);

            logger("Checkout id: {$this->checkout->id}, pagamento aprovado. Método: {$updateData['method_checked']}");
            $this->checkout->update($updateData);

            return;
        }

        // 2. Se não tiver aprovado, pega o mais recente
        $lastPayment = $paymentsCollection->first();

        if ($lastPayment && ($lastPayment['status'] ?? '') === 'rejected') {
            $updateData['status'] = StatusCheckoutEnum::rejeitado->value;
            $updateData['method_checked'] = $this->methodTransformer($lastPayment['method'] ?? null);

            logger("Checkout id: {$this->checkout->id}, pagamento reprovado. Método: {$updateData['method_checked']}");
            $this->checkout->update($updateData);

            return;
        }

        // 3. Se só tiver pendente ou nenhum -> não altera status
        logger("Checkout id: {$this->checkout->id}, aguardando pagamento (pendente ou nenhum relevante).");
        $this->checkout->update($updateData);
    }

    public function methodTransformer($methodMP)
    {
        if ($methodMP == 'pix') {
            return MethodPaymentEnum::pix->value;
        }

        if ($methodMP == 'bolbradesco') {
            return MethodPaymentEnum::billet->value;
        }

        return MethodPaymentEnum::credit_card->value;
    }
}
