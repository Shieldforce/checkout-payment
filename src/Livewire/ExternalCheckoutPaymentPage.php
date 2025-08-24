<?php

namespace Shieldforce\CheckoutPayment\Livewire;

use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Pages\SimplePage;
use Shieldforce\CheckoutPayment\Enums\TypeGatewayEnum;
use Shieldforce\CheckoutPayment\Pages\InternalCheckoutWizard;

class ExternalCheckoutPaymentPage extends SimplePage
{
    protected static string $view = 'checkout-payment::livewire.external-checkout-payment-page';

    protected static ?string $label = 'Checkout';

    public ?int $checkoutId = null;

    public array $data = [];

    public ?string $name = null;

    public ?string $email = null;

    public $paymentMethod = null;

    public ?TypeGatewayEnum $typeGateway = null;

    public function mount(?int $checkoutId = null): void
    {
        $this->checkoutId = $checkoutId;
        $this->typeGateway = config()->get('checkout-payment.type_gateway');
        $this->form->fill();
    }

    public static function getSlug(): string
    {
        return 'internal-checkout-payment/{checkoutId?}';
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Wizard::make(InternalCheckoutWizard::fieldWinzard()),
        ])
            ->statePath('data');
    }

    public function submit()
    {
        dd($this->form->getState());
    }
}
