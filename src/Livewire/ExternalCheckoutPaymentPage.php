<?php

namespace Shieldforce\CheckoutPayment\Livewire;

use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Pages\SimplePage;
use Illuminate\Support\Facades\Config;
use Shieldforce\CheckoutPayment\Pages\InternalCheckoutWizard;

class ExternalCheckoutPaymentPage extends SimplePage
{
    protected static string  $view       = "checkout-payment::livewire.external-checkout-payment-page";
    protected static ?string $label      = "Checkout";
    public ?int              $checkoutId = null;
    public array             $data       = [];
    public string            $name;
    public string            $email;
    public                   $paymentMethod;

    public function mount(?int $checkoutId = null): void
    {
        $this->checkoutId    = $checkoutId;
        $this->paymentMethod = config()->get('checkout-payment.type_gateway');
        $this->form->fill();
    }

    public static function getSlug(): string
    {
        return 'internal-checkout-payment/{checkoutId?}';
    }

    protected function form(Form $form): Form
    {
        $internal = new InternalCheckoutWizard();
        return $form->schema([
            Wizard::make($internal->fieldWinzard())
        ])
            ->statePath('data');
    }

    public function submit()
    {
        dd($this->form->getState());
    }

}
