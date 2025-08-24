<?php

namespace Shieldforce\CheckoutPayment\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Shieldforce\CheckoutPayment\CheckoutPaymentPlugin;

class CheckoutWizard extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-credit-card';
    protected static string  $view            = 'checkout-payment::pages.checkout-wizard';
    protected static ?string $navigationGroup = 'Checkout';
    protected static ?string $label           = "Página de Pagamento";
    protected static ?string $navigationLabel = "Página de Pagamento";

    public $name;
    public $email;
    public $paymentMethod;

    public ?int $checkoutId = null;

    /**
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        $plugin = new CheckoutPaymentPlugin();
        return $plugin->getLabelGroupSidebar();
    }

    public function mount(?int $checkoutId = null): void
    {
        $this->checkoutId = $checkoutId;
    }

    public static function getSlug(): string
    {
        return 'checkout-payment/{checkoutId?}';
    }

    protected function getFormSchema(): array
    {
        return [
            Wizard::make([
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
                                'boleto'      => 'Boleto'
                            ])
                            ->required(),
                    ]),
                Wizard\Step::make('Confirmação')
                    ->schema([
                        TextInput::make('review')->default('Revisar seus dados')->disabled(),
                    ]),
            ])
        ];
    }

    public function submit()
    {
        // Aqui você integra com API de pagamento
        dd([
            'name'          => $this->name,
            'email'         => $this->email,
            'paymentMethod' => $this->paymentMethod,
        ]);
    }
}
