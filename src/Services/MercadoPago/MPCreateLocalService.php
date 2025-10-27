<?php

namespace Shieldforce\CheckoutPayment\Services\MercadoPago;

use Carbon\Carbon;
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

    public $dateOfExpiration;

    public $totalPrice;

    public function __construct(public CppCheckout $checkout)
    {
        $this->mp = new MercadoPagoService;
        $this->step1 = $checkout?->step2()?->first();
        $this->step2 = $checkout?->step2()?->first();
        $this->step3 = $checkout?->step3()?->first();

        $this->dateOfExpiration = Carbon::createFromFormat('Y-m-d', $checkout->due_date)
            ->format("Y-m-d\TH:i:s") . '.000-04:00';

        if (! isset($step1->id) || ! isset($step2->id) || ! isset($step3->id)) {
            throw new CheckoutPaymentException('Etapa 1,2 e 3 são necessárias para gerar boleto!');
        }

        if (isset($step1->items)) {
            $items = json_decode($step1->items, true);
            $sum = 0;
            foreach ($items as $item) {
                $sum += $item['price'] * $item['quantity'];
            }

            $this->totalPrice = $sum;
        }

        $this->data = [
            'value' => isset($this->totalPrice) && $this->totalPrice > 0 ? (float) $this->totalPrice : null,
            'external_id' => $this->checkout->id ?? null,
            'payer_email' => $step2->email ?? null,
            'payer_first_name' => $step2->first_name ?? null,
            'payer_last_name' => $step2->last_name ?? null,
            'due_date' => $this->dateOfExpiration,
            'document' => $step2->document,
            'document_type' => TypePeopleEnum::from($step2->people_type)->mpLabel(),
            'address' => [
                'zip_code' => $step3->zipcode ?? null,
                'city' => $step3->city ?? null,
                'street_name' => $step3->street ?? null,
                'street_number' => $step3->number ?? null,
                'neighborhood' => $step3->district ?? null,
                'federal_unit' => $step3->state ?? null,
            ],
        ];
    }

    public function boleto()
    {
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
    }

    public function pix()
    {
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
    }
}
