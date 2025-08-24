<?php

namespace Shieldforce\CheckoutPayment\Services\MercadoPago;

use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use Shieldforce\CheckoutPayment\Enums\TypeGatewayEnum;
use Shieldforce\CheckoutPayment\Models\CppGateways;

class MercadoPagoPixService
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

    public function obertPagamento($paymentId)
    {
        if (!isset($paymentId)) {
            return null;
        }

        $client = new PaymentClient();
        return $client->get($paymentId);
    }

}
