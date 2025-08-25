<?php

namespace Shieldforce\CheckoutPayment;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\Facades\Route;
use Shieldforce\CheckoutPayment\Enums\TypeGatewayEnum;
use Shieldforce\CheckoutPayment\Pages\InternalCheckoutWizard;

class CheckoutPaymentPlugin implements Plugin
{
    public TypeGatewayEnum $typeGateway;
    public string          $labelGroupSidebar = "Checkout Payment";

    public function getId(): string
    {
        return 'checkout-payment';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->routes(function () {
                Route::get('/checkout/{cppCheckout?}', InternalCheckoutWizard::class)
                    ->name('checkout.external')
                    ->defaults('external', 1);
            })
            ->pages([
                //\Shieldforce\CheckoutPayment\Pages\InternalCheckoutWizard::class,
                \Shieldforce\CheckoutPayment\Pages\CPPGatewaysPage::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        config()->set('checkout-payment.sidebar_group', $this->labelGroupSidebar);
        config()->set('checkout-payment.type_gateway', $this->typeGateway);
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
    ): static
    {
        $this->typeGateway = $typeGateway;
        return $this;
    }

    public function setLabelGroupSidebar(
        string $labelGroupSidebar
    ): static
    {
        $this->labelGroupSidebar = $labelGroupSidebar;
        return $this;
    }

    public function getLabelGroupSidebar()
    {
        return $this->labelGroupSidebar;
    }
}
