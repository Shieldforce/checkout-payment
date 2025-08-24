<?php

namespace Shieldforce\CheckoutPayment\Enums;

enum TypeGatewayEnum: int
{
    case mercado_pago = 1;

    public function label(): string
    {
        return match ($this) {
            self::mercado_pago => 'Mercado Pago',
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
            default => [
                'field_1' => 'field_1',
                'field_2' => 'field_2',
                'field_3' => 'field_3',
                'field_4' => 'field_4',
                'field_5' => 'field_5',
                'field_6' => 'field_6',
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
            default => [
                'field_1' => false,
                'field_2' => false,
                'field_3' => false,
                'field_4' => false,
                'field_5' => false,
                'field_6' => false,
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
            default => [
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
            default => [
                'field_1' => false,
                'field_2' => false,
                'field_3' => false,
                'field_4' => false,
                'field_5' => false,
                'field_6' => false,
            ],
        };
    }
}
