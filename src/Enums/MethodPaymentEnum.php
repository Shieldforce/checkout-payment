<?php

namespace Shieldforce\CheckoutPayment\Enums;

enum MethodPaymentEnum: int
{
    case credit_card = 1;
    case debit_card  = 2;
    case pix         = 3;
    case billet      = 4;

    public function label(): string
    {
        return match ($this) {
            self::credit_card => 'Cartão de Crédito',
            self::debit_card  => 'Cartão de Débito',
            self::pix         => 'Pix',
            self::billet      => 'Boleto',
        };
    }
}
