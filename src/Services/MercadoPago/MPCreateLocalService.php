<?php

namespace Shieldforce\CheckoutPayment\Services\MercadoPago;

use Carbon\Carbon;
use Filament\Notifications\Notification;
use Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum;
use Shieldforce\CheckoutPayment\Enums\TypePeopleEnum;
use Shieldforce\CheckoutPayment\Errors\CheckoutPaymentException;
use Shieldforce\CheckoutPayment\Jobs\ProcessCheckoutUpdatePaymentsJob;
use Shieldforce\CheckoutPayment\Models\CppCheckout;

class MPCreateLocalService
{
    public array $data;

    public MercadoPagoService $mp;

    public $step1;

    public $step2;

    public $step3;

    public $step4;

    public $dateOfExpiration;

    public $totalPrice;

    /**
     * @param  string  $origin  'manual' (ação explícita de um usuário no painel) ou
     *                          'automatic' (disparado por um observer/job, sem clique direto).
     *                          Usado só para auditoria em cpp_checkout_billing_logs.
     */
    public function __construct(public CppCheckout $checkout, public string $origin = 'manual')
    {
        $this->mp = new MercadoPagoService;
        $this->step1 = $checkout?->step1()?->first();
        $this->step2 = $checkout?->step2()?->first();
        $this->step3 = $checkout?->step3()?->first();
        $this->step4 = $checkout?->step4()?->first();

        // O Mercado Pago recusa gerar boleto com vencimento no passado (fatura já vencida),
        // então quando isso acontece usamos uma data futura só para a expiração do boleto,
        // mantendo o due_date original da fatura como referência interna.
        $originalDueDate = Carbon::createFromFormat('Y-m-d', $checkout->due_date)->startOfDay();
        $expirationDate = $originalDueDate->isPast()
            ? Carbon::today()->addDays(5)
            : $originalDueDate;

        $this->dateOfExpiration = $expirationDate->format("Y-m-d\TH:i:s") . '.000-04:00';

        if (! isset($this->step1->id) || ! isset($this->step2->id) || ! isset($this->step3->id)) {
            throw new CheckoutPaymentException('Etapa 1,2 e 3 são necessárias para gerar boleto!');
        }

        if (isset($this->step1->items)) {
            $items = json_decode($this->step1->items, true);
            $sum = 0;
            foreach ($items as $item) {
                $sum += $item['price'] * $item['quantity'];
            }

            $this->totalPrice = $sum;
        }

        $this->data = [
            'value' => isset($this->totalPrice) && $this->totalPrice > 0 ? (float) $this->totalPrice : null,
            'external_id' => $this->checkout->uuid ?? null,
            'payer_email' => $this->step2->email ?? null,
            'payer_first_name' => $this->step2->first_name ?? null,
            'payer_last_name' => $this->step2->last_name ?? null,
            'due_date' => $this->dateOfExpiration,
            'document' => $this->step2->document,
            'document_type' => TypePeopleEnum::from($this->step2->people_type)->mpLabel(),
            'address' => [
                'zip_code' => $this->step3->zipcode ?? null,
                'city' => $this->step3->city ?? null,
                'street_name' => $this->step3->street ?? null,
                'street_number' => $this->step3->number ?? null,
                'neighborhood' => $this->step3->district ?? null,
                'federal_unit' => $this->step3->state ?? null,
            ],
        ];
    }

    public function boleto()
    {
        if (! empty($this->step4?->url_billet) && $this->billetUnchanged()) {
            $this->logAttempt('boleto', 'reused');

            return json_decode($this->step4->response_billet_data ?? '[]', true) ?: ['pdf' => $this->step4->url_billet];
        }

        $wasActive = ! empty($this->step4?->url_billet);

        if ($wasActive && $this->origin === 'automatic') {
            // Valor/vencimento mudaram, mas isso foi disparado sem um usuário confirmando a
            // troca (ex: salvar a transação de novo) — não cancela um boleto ativo sozinho.
            $this->logAttempt('boleto', 'skipped_change_detected');
            $this->notifyChangeDetected('boleto');

            return json_decode($this->step4->response_billet_data ?? '[]', true) ?: ['pdf' => $this->step4->url_billet];
        }

        if ($wasActive) {
            $oldPaymentId = json_decode($this->step4->response_billet_data ?? '[]', true)['id'] ?? null;
            if ($oldPaymentId) {
                $this->mp->cancelarPagamento($oldPaymentId);
            }
        }

        $return = $this->mp->gerarPagamentoBoleto(
            value: $this->data['value'],
            description: 'Pagamento via Boleto',
            external_id: $this->data['external_id'],
            payer_email: $this->data['payer_email'],
            payer_first_name: $this->data['payer_first_name'],
            payer_last_name: $this->data['payer_last_name'],
            due_date: $this->data['due_date'],
            document: $this->data['document'],
            document_type: $this->data['document_type'],
            address: $this->data['address']
        );

        logger($return);

        if (
            isset($return['data']['point_of_interaction']['transaction_data']['ticket_url']) ||
            isset($return['data']['transaction_details']['external_resource_url']) ||
            isset($return['pdf'])
        ) {
            $pdf = $return['data']['point_of_interaction']['transaction_data']['ticket_url'] ??
                $return['data']['transaction_details']['external_resource_url'] ??
                $return['pdf'];

            $this->checkout->step4()->updateOrCreate([
                'cpp_checkout_id' => $this->checkout->id,
            ], [
                'url_billet' => $pdf,
                'request_billet_data' => json_encode($this->data),
                'response_billet_data' => json_encode($return),
                'payment_method_id' => 'bolbradesco',
            ]);

            $this->checkout->update([
                'status' => StatusCheckoutEnum::pendente->value,
                'startOnStep' => 5,
            ]);

            $this->logAttempt('boleto', $wasActive ? 'regenerated' : 'generated', $return['id'] ?? null);
        }

        if (! isset($return['transaction_details']['external_resource_url'])) {
            $this->checkout->step4()->updateOrCreate([
                'cpp_checkout_id' => $this->checkout->id,
            ], [
                'request_billet_data' => json_encode($this->data),
                'response_billet_data' => json_encode($return),
            ]);

            // Atualizar o json das tentativas de pagamento -> campo (return_gateway)
            ProcessCheckoutUpdatePaymentsJob::dispatch($this->checkout);
        }

        return $return;
    }

    public function pix()
    {
        if (! empty($this->step4?->base_qrcode) && $this->pixUnchanged()) {
            $this->logAttempt('pix', 'reused');

            return json_decode($this->step4->response_pix_data ?? '[]', true) ?: ['qr_code_base64' => $this->step4->base_qrcode];
        }

        $wasActive = ! empty($this->step4?->base_qrcode);

        if ($wasActive && $this->origin === 'automatic') {
            // Valor mudou, mas isso foi disparado sem um usuário confirmando a troca (ex:
            // salvar a transação de novo) — não cancela um pix ativo sozinho.
            $this->logAttempt('pix', 'skipped_change_detected');
            $this->notifyChangeDetected('pix');

            return json_decode($this->step4->response_pix_data ?? '[]', true) ?: ['qr_code_base64' => $this->step4->base_qrcode];
        }

        if ($wasActive) {
            $oldPaymentId = json_decode($this->step4->response_pix_data ?? '[]', true)['id'] ?? null;
            if ($oldPaymentId) {
                $this->mp->cancelarPagamento($oldPaymentId);
            }
        }

        $return = $this->mp->gerarPagamentoPix(
            value: $this->data['value'],
            description: 'Pagamento via Pix',
            external_id: $this->data['external_id'],
            payer_email: $this->data['payer_email'],
            payer_first_name: $this->data['payer_first_name'],
        );

        logger($return);

        if (isset($return['qr_code_base64'])) {
            $this->checkout->step4()->updateOrCreate([
                'cpp_checkout_id' => $this->checkout->id,
            ], [
                'base_qrcode' => $return['qr_code_base64'],
                'url_qrcode' => $return['data']['point_of_interaction']['transaction_data']['ticket_url']
                    ?? $return['qr_code'],
                'request_pix_data' => json_encode($this->data),
                'response_pix_data' => json_encode($return),
                'payment_method_id' => 'pix',
            ]);

            $this->checkout->update([
                'status' => StatusCheckoutEnum::pendente->value,
                'startOnStep' => 5,
            ]);

            $this->logAttempt('pix', $wasActive ? 'regenerated' : 'generated', $return['id'] ?? null);
        }

        if (! isset($return['qr_code_base64'])) {
            $this->checkout->step4()->updateOrCreate([
                'cpp_checkout_id' => $this->checkout->id,
            ], [
                'request_pix_data' => json_encode($this->data),
                'response_pix_data' => json_encode($return),
            ]);

            // Atualizar o json das tentativas de pagamento -> campo (return_gateway)
            ProcessCheckoutUpdatePaymentsJob::dispatch($this->checkout);
        }

        return $return;
    }

    /**
     * Compara o boleto já salvo em step4 com o valor/vencimento que seria gerado agora.
     * Usa a mesma representação de due_date (dateOfExpiration) dos dois lados para não
     * disparar falso positivo por causa do ajuste de "vencimento no passado" do construtor.
     */
    private function billetUnchanged(): bool
    {
        $cached = json_decode($this->step4?->request_billet_data ?? '[]', true);

        return isset($cached['value'], $cached['due_date'])
            && abs((float) $cached['value'] - (float) $this->data['value']) < 0.01
            && $cached['due_date'] === $this->data['due_date'];
    }

    private function pixUnchanged(): bool
    {
        $cached = json_decode($this->step4?->request_pix_data ?? '[]', true);

        return isset($cached['value'])
            && abs((float) $cached['value'] - (float) $this->data['value']) < 0.01;
    }

    private function logAttempt(string $type, string $action, ?string $mpPaymentId = null): void
    {
        $this->checkout->billingLogs()->create([
            'type' => $type,
            'action' => $action,
            'origin' => $this->origin,
            'user_id' => auth()->id(),
            'value' => $this->data['value'] ?? null,
            'due_date' => $this->checkout->due_date,
            'mp_payment_id' => $mpPaymentId,
        ]);
    }

    private function notifyChangeDetected(string $type): void
    {
        Notification::make()
            ->title('Valor ou vencimento mudou, mas a cobrança ativa foi mantida')
            ->body(
                'O ' . ($type === 'pix' ? 'pix' : 'boleto') . ' já gerado pra esse checkout não foi'
                . ' cancelado automaticamente. Pra substituir por um novo valor/vencimento, gere'
                . ' manualmente pela tela de checkout ou pelo botão "Tentar Gerar Novamente".'
            )
            ->warning()
            ->persistent()
            ->send();
    }
}
