<?php

namespace Shieldforce\CheckoutPayment\Services\MercadoPago;

use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use Shieldforce\CheckoutPayment\Enums\TypeGatewayEnum;
use Shieldforce\CheckoutPayment\Models\CppGateways;

class MercadoPagoService
{
    public function __construct()
    {
        $cppGateways = CppGateways::where("name", TypeGatewayEnum::mercado_pago->value)
            ->where("active", true)
            ->first();

        MercadoPagoConfig::setAccessToken($cppGateways->field_2);
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

            return [
                'id'             => $payment->id ?? null,
                'qr_code_base64' => $payment->point_of_interaction->transaction_data->qr_code_base64 ?? null,
                'qr_code'        => $payment->point_of_interaction->transaction_data->qr_code ?? null,
                'status'         => $payment->status ?? null,
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
        $due_date
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
                    "email"      => $payer_email,
                    "first_name" => $payer_first_name
                ],
                "date_of_expiration" => $due_date,
            ]);

            return [
                'id'      => $payment->id ?? null,
                'barcode' => $payment->point_of_interaction->transaction_data->barcode ?? null,
                'pdf'     => $payment->point_of_interaction->transaction_data->ticket_url ?? null,
                'status'  => $payment->status ?? null,
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
        $token_card // token enviado pelo frontend
    )
    {
        try {
            $client = new PaymentClient();

            $payment = $client->create([
                "transaction_amount" => $value,
                "token"              => $token_card,
                "description"        => $description,
                "payment_method_id"  => "visa",
                "installments"       => 1,
                "payer"              => [
                    "email"      => $payer_email,
                    "first_name" => $payer_first_name
                ],
                "external_reference" => $external_id
            ]);

            return [
                'id'     => $payment->id ?? null,
                'status' => $payment->status ?? null,
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

    /*public function pay()
    {
        $cppGateways = CppGateways::where("name", TypeGatewayEnum::mercado_pago->value)
            ->where("active", true)
            ->first();

        MercadoPagoConfig::setAccessToken($cppGateways->field_2);

        $client          = new PaymentClient();
        $request_options = new RequestOptions();
        $request_options->setCustomHeaders(["X-Idempotency-Key: <SOME_UNIQUE_VALUE>"]);

        $createRequest = [
            "additional_info"         => [
                "items" => [
                    [
                        "id"                  => "MLB2907679857",
                        "title"               => "Point Mini",
                        "description"         => "Point product for card payments via Bluetooth.",
                        "picture_url"         => "https://http2.mlstatic.com/resources/frontend/statics/growth-sellers-landings/device-mlb-point-i_medium2x.png",
                        "category_id"         => "electronics",
                        "quantity"            => 1,
                        "unit_price"          => 58,
                        "type"                => "electronics",
                        "event_date"          => "2023-12-31T09:37:52.000-04:00",
                        "warranty"            => false,
                        "category_descriptor" => [
                            "passenger" => [],
                            "route"     => []
                        ]
                    ]
                ],
                "payer" => [
                    "first_name" => "Test",
                    "last_name"  => "Test",
                    "phone"      => [
                        "area_code" => 11,
                        "number"    => "987654321"
                    ],
                    "address"    => [
                        "street_number" => null
                    ],
                    "shipments"  => [
                        "receiver_address" => [
                            "zip_code"      => "12312-123",
                            "state_name"    => "Rio de Janeiro",
                            "city_name"     => "Buzios",
                            "street_name"   => "Av das Nacoes Unidas",
                            "street_number" => 3003
                        ],
                        "width"            => null,
                        "height"           => null
                    ]
                ],
            ],
            "application_fee"         => null,
            "binary_mode"             => false,
            "campaign_id"             => null,
            "capture"                 => false,
            "coupon_amount"           => null,
            "description"             => "Payment for product",
            "differential_pricing_id" => null,
            "external_reference"      => "MP0001",
            "installments"            => 1,
            "metadata"                => null,
            "payer"                   => [
                "entity_type"    => "individual",
                "type"           => "customer",
                "email"          => "test_user_123@testuser.com",
                "identification" => [
                    "type"   => "CPF",
                    "number" => "95749019047"
                ]
            ],
            "payment_method_id"       => "master",
            "token"                   => "ff8080814c11e237014c1ff593b57b4d",
            "transaction_amount"      => 58,
        ];

        $client->create($createRequest, $request_options);
    }*/

}
