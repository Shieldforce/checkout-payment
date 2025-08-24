<?php

namespace Shieldforce\CheckoutPayment\Enums;

enum StatusCheckoutEnum: int
{
    case criado     = 1;
    case enviado    = 2;
    case perdido    = 3;
    case pendente   = 4;
    case finalizado = 5;
    case erro       = 999;

    public function label(): string
    {
        return match ($this) {
            self::criado     => 'Criado',
            self::enviado    => 'Enviado',
            self::perdido    => 'Perdido',
            self::pendente   => 'Pendente',
            self::finalizado => 'Finalizado',
            self::erro       => 'Erro',
        };
    }
}
