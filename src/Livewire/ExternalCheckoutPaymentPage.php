<?php

namespace Shieldforce\CheckoutPayment\Livewire;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Pages\SimplePage;
use Shieldforce\CheckoutPayment\Pages\InternalCheckoutWizard;

class ExternalCheckoutPaymentPage extends SimplePage
{
    protected static string  $view       = "checkout-payment::livewire.external-checkout-payment-page";
    protected static ?string $label      = "Checkout";
    public ?int              $checkoutId = null;
    public string            $name;
    public string            $email;
    public                   $paymentMethod;

    public function mount(?int $checkoutId = null): void
    {
        $this->checkoutId = $checkoutId;
    }

    public static function getSlug(): string
    {
        return 'internal-checkout-payment/{checkoutId?}';
    }

    protected function getFormSchema(): array
    {
        $internal = new InternalCheckoutWizard();
        return [
            Wizard::make($internal->fieldWinzard())
        ];
    }

    public function submit()
    {
        dd([
            'name'          => $this->name,
            'email'         => $this->email,
            'paymentMethod' => $this->paymentMethod,
        ]);
    }

}
