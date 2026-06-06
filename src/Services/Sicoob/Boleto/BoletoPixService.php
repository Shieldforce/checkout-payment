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

        /*{
          "numeroCliente": 25546454,
          "codigoModalidade": 1,
          "numeroContaCorrente": 123456,
          "codigoEspecieDocumento": "DM",
          "dataEmissao": "2025-09-01",
          "nossoNumero": "0123456789012345678",
          "seuNumero": "012345678901234567",
          "identificacaoBoletoEmpresa": "01234567890123456789",
          "identificacaoEmissaoBoleto": 1,
          "identificacaoDistribuicaoBoleto": 1,
          "valor": 156.23,
          "dataVencimento": "2025-09-25",
          "dataLimitePagamento": "2025-11-05",
          "valorAbatimento": 5.57,
          "tipoDesconto": 1,
          "dataPrimeiroDesconto": "2025-09-15",
          "valorPrimeiroDesconto": 22.5,
          "dataSegundoDesconto": "2025-09-20",
          "valorSegundoDesconto": 15.5,
          "dataTerceiroDesconto": "2025-09-24",
          "valorTerceiroDesconto": 10.5,
          "tipoMulta": 1,
          "dataMulta": "2025-11-05",
          "valorMulta": 5.5,
          "tipoJurosMora": 1,
          "dataJurosMora": "2025-11-05",
          "valorJurosMora": 4.5,
          "numeroParcela": 1,
          "aceite": true,
          "codigoNegativacao": 2,
          "numeroDiasNegativacao": 60,
          "codigoProtesto": 1,
          "numeroDiasProtesto": 30,
          "pagador": {
                    "numeroCpfCnpj": "11122233300",
            "nome": "Nome completo do pagador X",
            "endereco": "Endereço do pagador X",
            "bairro": "Bairro do pagador X",
            "cidade": "Cidade do pagador X",
            "cep": "00000000",
            "uf": "OU",
            "email": "pagador@dominio.com br"
          },
          "beneficiarioFinal": {
                    "numeroCpfCnpj": "11122233300",
            "nome": "Beneficiário Y"
          },
          "mensagensInstrucao": [
                    "Descrição da Instrução 1",
                    "Descrição da Instrução 2",
                    "Descrição da Instrução 3",
                    "Descrição da Instrução 4",
                    "Descrição da Instrução 5"
                ],
          "gerarPdf": false,
          "rateioCreditos": [
            {
                "numeroBanco": 33,
              "numeroAgencia": 1,
              "numeroContaCorrente": "987654",
              "contaPrincipal": true,
              "codigoTipoValorRateio": 1,
              "valorRateio": "100",
              "codigoTipoCalculoRateio": 1,
              "numeroCpfCnpjTitular": "11122233300",
              "nomeTitular": "Nome completo do titular X",
              "codigoFinalidadeTed": "10",
              "codigoTipoContaDestinoTed": "CC",
              "quantidadeDiasFloat": 1,
              "dataFloatCredito": "2020-12-30"
            }
          ],
          "codigoCadastrarPIX": 1,
          "numeroContratoCobranca": 1
        }*/


        $payload = [
            "numeroCliente"                   => $dados["numero_cliente"],
            "codigoModalidade"                => 1,
            "numeroContaCorrente"             => $dados["numero_conta"],
            "codigoEspecieDocumento"          => "DM",
            "dataEmissao"                     => date("Y-m-d"),
            "seuNumero"                       => (string) $dados["external_reference"],
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
                "nome"          => $this->limpaNome($dados["pagador"]["nome"]),
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
            CURLOPT_VERBOSE => true,
        ]);

        $response = curl_exec($curl);

        dd([
            'http_code' => curl_getinfo($curl, CURLINFO_HTTP_CODE),
            'response' => $response,
        ]);

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

    private function limpaNome($valor, $limite = 40)
    {
        $valor = mb_convert_encoding($valor, 'UTF-8', 'auto');
        $valor = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor);
        $valor = preg_replace('/[^A-Za-z0-9 ]/', '', $valor);
        $valor = preg_replace('/\s+/', ' ', $valor);
        $valor = trim($valor);

        return substr($valor, 0, $limite);
    }
}
