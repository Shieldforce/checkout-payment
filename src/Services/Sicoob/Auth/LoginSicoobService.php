<?php

namespace Shieldforce\CheckoutPayment\Services\Sicoob\Auth;

class LoginSicoobService
{
    public function auth($dados): array
    {
        $curl = curl_init();

        $payload = [
            "client_id"         => $dados['client_id'],
            "path_certificado"  => $dados['path_certificado'],
            "senha_certificado" => $dados['senha_certificado'],
        ];

        $url = "https://auth.sicoob.com.br/auth/realms/cooperado/protocol/openid-connect/token";

        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type' => 'client_credentials',
                'client_id'  => $payload["client_id"],
                'scope'      => 'boletos_inclusao boletos_consulta boletos_alteracao',
            ]),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
            ],

            // 🔐 CERTIFICADO DIGITAL
            CURLOPT_SSLCERTTYPE    => 'P12',
            CURLOPT_SSLCERT        => $payload["path_certificado"],
            CURLOPT_SSLCERTPASSWD  => $payload["senha_certificado"],

            // ⚠️ IMPORTANTE (ambiente real)
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            throw new \Exception('Erro cURL: ' . curl_error($curl));
        }

        curl_close($curl);

        return json_decode($response, true);
    }
}
