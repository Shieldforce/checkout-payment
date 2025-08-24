<?php

namespace Shieldforce\CheckoutPayment\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

class CPPGatewaysPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string  $view            = 'checkout-payment::pages.cpp_gateways';
    protected static ?string $navigationIcon  = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Gateways';
    protected static ?string $label           = 'Gateway';
    protected static ?string $navigationLabel = 'Gateway';

    public function mount(?int $checkoutId = null): void
    {

    }

    protected function getFormSchema(): array
    {
        return [];
    }

    public static function getNavigationGroup(): ?string
    {
        return config()->get('checkout-payment.sidebar_group');;
    }
}

