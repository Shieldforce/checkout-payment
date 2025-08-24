<?php

namespace Shieldforce\CheckoutPayment\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Shieldforce\CheckoutPayment\Enums\TypeGatewayEnum;

class InternalCheckoutWizard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string  $view            = 'checkout-payment::pages.internal-checkout-wizard';
    protected static ?string $navigationIcon  = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Pagamentos';
    protected static ?string $label           = 'Checkout';
    protected static ?string $navigationLabel = 'Checkout';

    public ?int             $checkoutId    = null;
    public array            $data          = [];
    public ?string          $name          = null;
    public ?string          $email         = null;
    public                  $paymentMethod = null;
    public ?TypeGatewayEnum $typeGateway   = null;

    public function mount(?int $checkoutId = null): void
    {
        if (!Auth::check()) {
            filament()
                ->getCurrentPanel()
                ->topNavigation()
                ->topbar(false);
        }

        $this->checkoutId  = $checkoutId;
        $this->typeGateway = config()->get('checkout-payment.type_gateway');
        $this->form->fill();
    }

    public static function getSlug(): string
    {
        return 'internal-checkout-payment/{checkoutId?}';
    }

    public static function fieldWinzard()
    {
        return [
            Wizard\Step::make('Cliente')
                ->schema([
                    TextInput::make('name')->required()->label('Nome'),
                    TextInput::make('email')->required()->email()->label('Email'),
                ]),
            Wizard\Step::make('Pagamento')
                ->schema([
                    Select::make('paymentMethod')
                        ->options([
                            'pix'         => 'PIX',
                            'credit_card' => 'Cartão de Crédito',
                            'boleto'      => 'Boleto',
                        ])
                        ->required(),
                ]),
            Wizard\Step::make('Confirmação')
                ->schema([
                    TextInput::make('review')->default('Revisar seus dados')->disabled(),
                ]),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Wizard::make($this->fieldWinzard()),
        ];
    }

    public function submit()
    {
        dd($this->form->getState());
    }

    public function getLayout(): string
    {
        if (request()->query('external') === '1') {
            return 'checkout-payment::layouts.external';
        }

        return parent::getLayout();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getNavigationGroup(): ?string
    {
        return config()->get('checkout-payment.sidebar_group');;
    }
}

