<?php

namespace Shieldforce\CheckoutPayment;

use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Shieldforce\CheckoutPayment\Enums\TypeGatewayEnum;
use Shieldforce\CheckoutPayment\Pages\CheckoutWizard;

class CheckoutPaymentPlugin implements Plugin
{
    public TypeGatewayEnum $typeGateway;

    public function getId(): string
    {
        return 'checkout-payment';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages([
                \Shieldforce\CheckoutPayment\Pages\CheckoutWizard::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function setTypeGateway(
        $typeGateway
    )
    {
        $this->typeGateway = $typeGateway;
        return $this;
    }
}
