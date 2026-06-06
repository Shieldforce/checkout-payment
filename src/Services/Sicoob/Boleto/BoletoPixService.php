<?php

namespace Shieldforce\CheckoutPayment\Services\Sicoob\Boleto;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;

class BoletoPixService
{

    public function __construct(public string $token) {}

    public function insert($dados)
    {
        $payload = [
            "numeroCliente"                   => $dados["numero_cliente"],
            "codigoModalidade"                => 1,
            "numeroContaCorrente"             => $dados["numero_conta"],
            "codigoEspecieDocumento"          => "DM",
            "dataEmissao"                     => date("Y-m-d"),
            "seuNumero"                       => $dados["external_reference"],
            "identificacaoEmissaoBoleto"      => 1,
            "identificacaoDistribuicaoBoleto" => 1,
            "valor"                           => (float)$dados["value"],
            "dataVencimento"                  => $dados["due"],
            "dataLimitePagamento"             => Carbon::parse($dados["due"])->addDays(60)->format("Y-m-d"),
            "tipoDesconto"                    => 0,
            "tipoMulta"                       => 1,
            "dataMulta"                       => Carbon::parse($dados["due"])->addDays(2)->format("Y-m-d"),
            "valorMulta"                      => 2,
            "tipoJurosMora"                   => 1,
            "dataJurosMora"                   => Carbon::parse($dados["due"])->addDays(3)->format("Y-m-d"),
            "valorJurosMora"                  => 0.01,
            "numeroParcela"                   => 1,
            "pagador"                         => [
                "numeroCpfCnpj" => $dados["pagador"]["numeroCpfCnpj"],
                "nome"          => $dados["pagador"]["nome"],
                "endereco"      => Str::upper(Str::ascii($dados["pagador"]["endereco"])),
                "bairro"        => Str::upper(Str::ascii($dados["pagador"]["bairro"])),
                "cidade"        => Str::upper(Str::ascii($dados["pagador"]["cidade"])),
                "cep"           => $dados["pagador"]["cep"],
                "uf"            => Str::upper(Str::ascii($dados["pagador"]["uf"])),
                "email"         => $dados["pagador"]["email"],
            ],
            "beneficiarioFinal"               => [
                "numeroCpfCnpj" => $dados["beneficiarioFinal"]["numeroCpfCnpj"],
                "nome"          => $dados["beneficiarioFinal"]["nome"],
            ],
            "mensagensInstrucao"              => $dados["mensagensInstrucao"],
            "gerarPdf"                        => true,
            "codigoCadastrarPIX"              => 1,
            "numeroContratoCobranca"          => $dados["numeroContratoCobranca"],
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://api.sicoob.com.br/cobranca-bancaria/v3/boletos',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $this->token,
                'client_id: ' . $dados["client_id"],
            ],

            // 🔐 Certificado
            CURLOPT_SSLCERTTYPE    => 'P12',
            CURLOPT_SSLCERT        => $dados["path_certificado"],
            CURLOPT_SSLCERTPASSWD  => $dados["senha_certificado"],

            // SSL
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,

            // Debug opcional
            // CURLOPT_VERBOSE => true,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            throw new Exception('Erro cURL: ' . curl_error($curl));
        }

        curl_close($curl);

        return json_decode($response, true) ?? false;
    }

    public function consult($dados): array|bool
    {

        $link = "https://api.sicoob.com.br/cobranca-bancaria/v3/boletos";
        $link .= "?numeroCliente={$dados['numero_cliente']}";
        $link .= "&codigoModalidade=1&nossoNumero={$dados['nosso_numero']}";
        $link .= "&numeroContratoCobranca={$dados['numero_contrato']}";
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => $link,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'GET',

            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: Bearer ' . $this->token,
                'client_id: ' . $dados['client_id'],
            ],

            CURLOPT_SSLCERTTYPE    => 'P12',
            CURLOPT_SSLCERT        => $dados["path_certificado"],
            CURLOPT_SSLCERTPASSWD  => $dados["senha_certificado"],

            // SSL
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            throw new Exception('Erro cURL: ' . curl_error($curl));
        }

        curl_close($curl);

        return json_decode($response, true) ?? false;
    }

    public function update(
        $nossoNumero,
        $token,
        $dados,
    )
    {
        $payload = [
            "numeroCliente"                   => $dados["numero_cliente"],
            "codigoModalidade"                => 1,
            "numeroContaCorrente"             => $dados["numero_conta"],
            "codigoEspecieDocumento"          => "DM",
            "dataEmissao"                     => date("Y-m-d"),
            "seuNumero"                       => $dados["external_reference"],
            "identificacaoEmissaoBoleto"      => 1,
            "identificacaoDistribuicaoBoleto" => 1,
            "valor"                           => $dados["value"],
            "dataVencimento"                  => $dados["due"],
            "dataLimitePagamento"             => Carbon::parse($dados["due"])->addDays(60)->format("Y-m-d"),
            "tipoDesconto"                    => 0,
            "tipoMulta"                       => 1,
            "dataMulta"                       => Carbon::parse($dados["due"])->addDays(2)->format("Y-m-d"),
            "valorMulta"                      => 2,
            "tipoJurosMora"                   => 1,
            "dataJurosMora"                   => Carbon::parse($dados["due"])->addDays(3)->format("Y-m-d"),
            "valorJurosMora"                  => 0.01,
            "numeroParcela"                   => 1,
            "pagador"                         => [
                "numeroCpfCnpj" => $dados["pagador"]["numeroCpfCnpj"],
                "nome"          => $dados["pagador"]["nome"],
                "endereco"      => $dados["pagador"]["endereco"],
                "bairro"        => $dados["pagador"]["bairro"],
                "cidade"        => $dados["pagador"]["cidade"],
                "cep"           => $dados["pagador"]["cep"],
                "uf"            => $dados["pagador"]["uf"],
                "email"         => $dados["pagador"]["email"],
            ],
            "beneficiarioFinal"               => [
                "numeroCpfCnpj" => $dados["empresaCnpj"],
                "nome"          => $dados["empresa"]
            ],
            "mensagensInstrucao"              => $dados["mensagensInstrucao"],
            "gerarPdf"                        => true,
            "codigoCadastrarPIX"              => 1,
            "numeroContratoCobranca"          => $dados["numero_contrato"]
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => "https://api.sicoob.com.br/cobranca-bancaria/v3/boletos/{$nossoNumero}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $token,
                'client_id: ' . $dados["client_id"],
            ],

            // 🔐 Certificado
            CURLOPT_SSLCERTTYPE    => 'P12',
            CURLOPT_SSLCERT        => $dados["path_certificado"],
            CURLOPT_SSLCERTPASSWD  => $dados["senha_certificado"],

            // SSL
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,

            // Debug opcional
            // CURLOPT_VERBOSE => true,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            throw new Exception('Erro cURL: ' . curl_error($curl));
        }

        curl_close($curl);

        return json_decode($response, true) ?? false;
    }
}
