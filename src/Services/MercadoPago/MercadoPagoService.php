<?php

namespace Shieldforce\CheckoutPayment\Services\MercadoPago;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Net\MPSearchRequest;
use Shieldforce\CheckoutPayment\Enums\TypeGatewayEnum;
use Shieldforce\CheckoutPayment\Models\CppGateways;

class MercadoPagoService
{
    public function __construct()
    {
        try {
            $cppGateways = CppGateways::where("name", TypeGatewayEnum::mercado_pago->value)
                ->where("active", true)
                ->first();

            if (/*!App::runningInConsole() && */ Schema::hasTable('cpp_gateways')) {
                MercadoPagoConfig::setAccessToken(Crypt::decrypt($cppGateways->field_2));
            }
        } catch (\Exception $e) {}
    }

    public function gerarPagamentoPix(
        $value,
        $description,
        $external_id,
        $payer_email,
        $payer_first_name,
    )
    {
        try {
            $client = new PaymentClient();

            $payment = $client->create([
                "transaction_amount" => $value,
                "description"        => $description,
                "payment_method_id"  => "pix",
                "external_reference" => $external_id,
                "payer"              => [
                    "email"      => $payer_email,
                    "first_name" => $payer_first_name
                ]
            ]);

            $arrayPayment = json_decode(json_encode($payment), true);

            return [
                'id'             => $payment->id ?? null,
                'qr_code_base64' => $payment->point_of_interaction->transaction_data->qr_code_base64 ?? null,
                'qr_code'        => $payment->point_of_interaction->transaction_data->qr_code ?? null,
                'status'         => $payment->status ?? null,
                "data"           => $arrayPayment ?? null
            ];
        } catch (MPApiException $e) {

            $code = $e->getApiResponse()->getStatusCode();
            $msg  = $e->getApiResponse()->getContent();

            logger([
                'code' => $code,
                'msg'  => $msg,
            ]);

            return [];

        } catch (\Throwable $e) {
            logger("Erro ao gerar pagamento: " . $e->getMessage());

            return [];
        }
    }

    public function gerarPagamentoBoleto(
        $value,
        $description,
        $external_id,
        $payer_email,
        $payer_first_name,
        $payer_last_name,
        $due_date,
        $document,
        $document_type,
        $address
    )
    {
        try {
            $client = new PaymentClient();

            $payment = $client->create([
                "transaction_amount" => $value,
                "description"        => $description,
                "payment_method_id"  => "bolbradesco",
                "external_reference" => $external_id,
                "payer"              => [
                    "email"          => $payer_email,
                    "first_name"     => $payer_first_name,
                    "last_name"      => $payer_last_name,
                    "identification" => [
                        "type"   => $document_type,
                        "number" => $document
                    ],
                    "address"        => [
                        "zip_code"      => $address["zip_code"],
                        "city"          => $address["city"] ?? null,
                        "street_name"   => $address["street_name"] ?? null,
                        "street_number" => !empty($address["street_number"]) ? $address["street_number"] : "s/n",
                        "neighborhood"  => $address["neighborhood"] ?? null,
                        "federal_unit"  => $address["federal_unit"] ?? "RJ"
                    ]
                ],
                "date_of_expiration" => $due_date,
            ]);

            $arrayPayment = json_decode(json_encode($payment), true);

            return [
                'id'      => $payment->id ?? null,
                'barcode' => $payment->transaction_details->barcode->content ?? null,
                'pdf'     => $payment->transaction_details->external_resource_url ?? null,
                'status'  => $payment->status ?? null,
                'data'    => $arrayPayment ?? null,
            ];
        } catch (MPApiException $e) {
            $code = $e->getApiResponse()->getStatusCode();
            $msg  = $e->getApiResponse()->getContent();

            logger([
                'code' => $code,
                'msg'  => $msg,
            ]);

            return [];
        } catch (\Throwable $e) {
            logger("Erro ao gerar pagamento boleto: " . $e->getMessage());

            return [];
        }
    }

    public function gerarPagamentoCartao(
        $value,
        $description,
        $external_id,
        $payer_email,
        $payer_first_name,
        $token_card, // token enviado pelo frontend
        $installments,
        $payment_method_id,
    )
    {
        try {
            $client = new PaymentClient();

            $payment = $client->create([
                "transaction_amount" => $value,
                "token"              => $token_card,
                "description"        => $description,
                "payment_method_id"  => $payment_method_id,
                "installments"       => $installments,
                "payer"              => [
                    "email"      => $payer_email,
                    "first_name" => $payer_first_name
                ],
                "external_reference" => $external_id
            ]);

            $arrayPayment = json_decode(json_encode($payment), true);

            return [
                'id'     => $payment->id ?? null,
                'status' => $payment->status ?? null,
                'data'   => $arrayPayment ?? null,
            ];
        } catch (\Throwable $e) {
            logger("Erro ao gerar pagamento cartão: " . $e->getMessage());
            return [];
        }
    }

    public function obertPagamento($paymentId)
    {
        if (!isset($paymentId)) {
            return null;
        }

        $client = new PaymentClient();
        return $client->get($paymentId);
    }

    public function buscarPagamentoPorExternalId($externalId)
    {
        if (!$externalId) {
            return [];
        }

        try {
            $client = new PaymentClient();

            $payments = $client->search(
                request: new MPSearchRequest(
                    50,
                    0,
                    filters: [
                        'external_reference' => $externalId,
                    ]
                )
            );

            $results = $payments->results ?? [];

            $result = [];

            foreach ($results as $payment) {
                $arrayPayment = json_decode(json_encode($payment), true);

                $result[] = [
                    'id'     => $payment->id ?? null,
                    'status' => $payment->status ?? null,
                    'method' => $payment->payment_method_id ?? null,
                    'data'   => $arrayPayment,
                ];
            }

            return $result;

        } catch (MPApiException $e) {
            $code = $e->getApiResponse()->getStatusCode();
            $msg  = $e->getApiResponse()->getContent();

            logger([
                'code' => $code,
                'msg'  => $msg,
            ]);

            return [];
        } catch (\Throwable $e) {
            logger("Erro ao buscar pagamento por external_id: " . $e->getMessage());
            return [];
        }
    }


}
