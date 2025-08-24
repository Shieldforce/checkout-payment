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

    public static function labelFields(?string $fieldName = null): string
    {
        if(!isset($fieldName)) {
            return 'Campo';
        }

        $return = [
            'field_1' => 'TOKEN',
        ];

        return $return[$fieldName] ?? $fieldName;
    }
}
