<?php

namespace Shieldforce\CheckoutPayment\Enums;

enum TypeGatewayEnum: int
{
    case mercado_pago = 1;
    case sicoob       = 2;

    public function label(): string
    {
        return match ($this) {
            self::mercado_pago => 'Mercado Pago',
            self::sicoob       => 'Sicoob',
        };
    }

    public function labelFields(): array
    {
        return match ($this) {
            self::mercado_pago => [
                'field_1' => 'MP_PUBLIC_KEY',
                'field_2' => 'MP_ACCESS_TOKEN',
                'field_3' => 'TOKEN3',
                'field_4' => 'TOKEN4',
                'field_5' => 'TOKEN5',
                'field_6' => 'TOKEN6',
            ],
            self::sicoob       => [
                'field_1' => 'senha_certificado',
                'field_2' => 'client_id',
                'field_3' => 'numero_contrato',
                'field_4' => 'numero_cliente',
                'field_5' => 'path_certificado',
                'field_6' => 'numero_conta',
            ],
        };
    }

    public function required(): array
    {
        return match ($this) {
            self::mercado_pago => [
                'field_1' => true,
                'field_2' => true,
                'field_3' => false,
                'field_4' => false,
                'field_5' => false,
                'field_6' => false,
            ],
            self::sicoob       => [
                'field_1' => true,
                'field_2' => true,
                'field_3' => true,
                'field_4' => true,
                'field_5' => true,
                'field_6' => true,
            ],
        };
    }

    public function visible(): array
    {
        return match ($this) {
            self::mercado_pago => [
                'field_1' => true,
                'field_2' => true,
                'field_3' => false,
                'field_4' => false,
                'field_5' => false,
                'field_6' => false,
            ],
            self::sicoob       => [
                'field_1' => true,
                'field_2' => true,
                'field_3' => true,
                'field_4' => true,
                'field_5' => true,
                'field_6' => true,
            ],
        };
    }

    public function password(): array
    {
        return match ($this) {
            self::mercado_pago => [
                'field_1' => true,
                'field_2' => true,
                'field_3' => false,
                'field_4' => false,
                'field_5' => false,
                'field_6' => false,
            ],
            self::sicoob       => [
                'field_1' => true,
                'field_2' => true,
                'field_3' => true,
                'field_4' => true,
                'field_5' => true,
                'field_6' => true,
            ],
        };
    }

    public function limit(): array
    {
        return match ($this) {
            self::mercado_pago => [
                'field_1' => 30,
                'field_2' => 15,
                'field_3' => 15,
                'field_4' => 30,
                'field_5' => 30,
                'field_6' => 30,
            ],
            self::sicoob       => [
                'field_1' => 30,
                'field_2' => 30,
                'field_3' => 30,
                'field_4' => 30,
                'field_5' => 30,
                'field_6' => 30,
            ],
        };
    }

    public function tooltip(): array
    {
        return match ($this) {
            self::mercado_pago => [
                'field_1' => 'Gateway',
                'field_2' => 'Campo sensível',
                'field_3' => 'Campo sensível',
                'field_4' => '-',
                'field_5' => '-',
                'field_6' => '-',
            ],
            self::sicoob       => [
                'field_1' => 'senha_certificado',
                'field_2' => 'client_id',
                'field_3' => 'numero_contrato',
                'field_4' => 'numero_cliente',
                'field_5' => 'path_certificado',
                'field_6' => 'numero_conta',
            ],
        };
    }

    public function description(): array
    {
        return match ($this) {
            self::mercado_pago => [
                'field_1' => '-',
                'field_2' => 'Campo sensível',
                'field_3' => 'Campo sensível',
                'field_4' => '-',
                'field_5' => '-',
                'field_6' => '-',
            ],
            self::sicoob       => [
                'field_1' => 'senha_certificado',
                'field_2' => 'client_id',
                'field_3' => 'numero_contrato',
                'field_4' => 'numero_cliente',
                'field_5' => 'path_certificado',
                'field_6' => 'numero_conta',
            ],
        };
    }
}
