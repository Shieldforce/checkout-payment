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
                "field_1" => "TOKEN"
            ],
        };
    }
}
