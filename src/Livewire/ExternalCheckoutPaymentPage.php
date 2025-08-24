<?php

namespace Shieldforce\CheckoutPayment\Livewire;

use Filament\Forms\Components\Wizard;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Shieldforce\CheckoutPayment\Enums\TypeGatewayEnum;
use Shieldforce\CheckoutPayment\Pages\InternalCheckoutWizard;

class ExternalCheckoutPaymentPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'checkout-payment::livewire.external-checkout-payment-page';

    protected static ?string $label = 'Checkout';

    protected static bool $shouldRegisterNavigation = false;

    public ?int $checkoutId = null;

    public array $data = [];

    public ?string $name = null;

    public ?string $email = null;

    public $paymentMethod = null;

    public ?TypeGatewayEnum $typeGateway = null;

    public function mount(?int $checkoutId = null): void
    {
        filament()
            ->getCurrentPanel()
            ->topNavigation()
            /*->topbar(false)*/;
        $this->checkoutId = $checkoutId;
        $this->typeGateway = config()->get('checkout-payment.type_gateway');
        $this->form->fill();
    }

    public static function getSlug(): string
    {
        return 'internal-checkout-payment/{checkoutId?}';
    }

    public function getFormSchema() : array
    {
        return [
            Wizard::make(InternalCheckoutWizard::fieldWinzard()),
        ];
    }

    public function submit()
    {
        dd($this->form->getState());
    }
}
