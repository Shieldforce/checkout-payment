<?php

namespace Shieldforce\CheckoutPayment\Enums;

enum TypeStepEnum: int
{
    case items          = 1;
    case dados_pessoais = 2;
    case dados_endereco = 3;
    case pagamento      = 4;
    case finalizado     = 5;

    public function label(): string
    {
        return match ($this) {
            self::items          => 'Carrinho',
            self::dados_pessoais => 'Dados Pessoais',
            self::dados_endereco => 'Dados de Endereco',
            self::pagamento      => 'Pagamento',
            self::finalizado     => 'Finalizado',
        };
    }
}
