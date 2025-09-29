<?php

namespace Shieldforce\CheckoutPayment\Enums;

enum StatusCheckoutEnum: int
{
    case criado     = 1;
    case enviado    = 2;
    case perdido    = 3;
    case pendente   = 4;
    case finalizado = 5;
    case rejeitado  = 6;
    case erro       = 999;

    public function label(): string
    {
        return match ($this) {
            self::criado     => 'Criado',
            self::enviado    => 'Enviado',
            self::perdido    => 'Perdido',
            self::pendente   => 'Pendente',
            self::finalizado => 'Finalizado',
            self::rejeitado  => 'Rejeitado',
            self::erro       => 'Erro',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::criado     => 'success',
            self::enviado    => 'primary',
            self::perdido    => 'danger',
            self::pendente   => 'warning',
            self::finalizado => 'success',
            self::rejeitado  => 'danger',
            self::erro       => 'danger',
        };
    }

    public static function labelEnum($state): string
    {
        return match ($state) {
            self::criado->value     => self::criado->label(),
            self::enviado->value    => self::enviado->label(),
            self::perdido->value    => self::perdido->label(),
            self::pendente->value   => self::pendente->label(),
            self::finalizado->value => self::finalizado->label(),
            self::rejeitado->value  => self::rejeitado->label(),
            self::erro->value       => self::erro->label(),
        };
    }

    public static function colorEnum($state): string
    {
        return match ($state) {
            self::criado->value     => self::criado->color(),
            self::enviado->value    => self::enviado->color(),
            self::perdido->value    => self::perdido->color(),
            self::pendente->value   => self::pendente->color(),
            self::finalizado->value => self::finalizado->color(),
            self::rejeitado->value  => self::rejeitado->color(),
            self::erro->value       => self::erro->color(),
        };
    }
}
