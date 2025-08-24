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
        if (!isset($fieldName)) {
            return 'Campo';
        }

        $return = [
            'field_1' => 'TOKEN1',
            'field_2' => 'TOKEN2',
            'field_3' => 'TOKEN3',
            'field_4' => 'TOKEN4',
            'field_5' => 'TOKEN5',
            'field_6' => 'TOKEN6',
        ];

        return $return[$fieldName] ?? $fieldName;
    }

    public static function required(?string $fieldName = null): string
    {
        if (!isset($fieldName)) {
            return false;
        }

        $return = [
            'field_1' => true,
            'field_2' => true,
            'field_3' => false,
            'field_4' => false,
            'field_5' => false,
            'field_6' => false,
        ];

        return $return[$fieldName] ?? $fieldName;
    }
}
