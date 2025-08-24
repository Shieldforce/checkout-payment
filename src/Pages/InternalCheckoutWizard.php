<?php

namespace Shieldforce\CheckoutPayment\Pages;

use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Pages\Page;

class InternalCheckoutWizard extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static string $view = 'checkout-payment::pages.internal-checkout-wizard';

    protected static ?string $navigationGroup = 'Pagamentos';

    protected static ?string $label = 'Checkout';

    protected static ?string $navigationLabel = 'Checkout';

    public ?int $checkoutId = null;

    public array $data = [];

    public string $name;

    public string $email;

    public $paymentMethod;

    public static function getNavigationGroup(): ?string
    {
        return config('checkout-payment.sidebar_group') ?? 'Pagamentos';
    }

    public function mount(?int $checkoutId = null): void
    {
        $this->checkoutId = $checkoutId;
        $this->paymentMethod = config()->get('checkout-payment.type_gateway');
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
                            'pix' => 'PIX',
                            'credit_card' => 'Cartão de Crédito',
                            'boleto' => 'Boleto',
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
}
