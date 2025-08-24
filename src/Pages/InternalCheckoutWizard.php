<?php

namespace Shieldforce\CheckoutPayment\Pages;

use App\Services\ApiCpfCnpjService;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Shieldforce\CheckoutPayment\Enums\TypeGatewayEnum;
use Shieldforce\CheckoutPayment\Enums\TypePeopleEnum;
use Shieldforce\CheckoutPayment\Models\CppCheckout;
use Shieldforce\CheckoutPayment\Models\CppCheckoutStep1;
use Shieldforce\CheckoutPayment\Models\CppCheckoutStep2;
use Shieldforce\CheckoutPayment\Models\CppCheckoutStep3;
use Shieldforce\CheckoutPayment\Models\CppCheckoutStep4;
use Shieldforce\CheckoutPayment\Models\CppGateways;

class InternalCheckoutWizard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'checkout-payment::pages.internal-checkout-wizard';

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Pagamentos';

    protected static ?string $label = 'Checkout';

    protected static ?string $navigationLabel = 'Checkout';

    protected static ?string $title = 'Realizar Pagamento';

    public array $data = [];

    public ?string $people_type  = null;
    public ?string $first_name   = null;
    public ?string $last_name    = null;
    public ?string $email        = null;
    public ?string $phone_number = null;
    public ?string $document     = null;

    public ?CppCheckoutStep1 $step1 = null;
    public ?CppCheckoutStep2 $step2 = null;
    public ?CppCheckoutStep3 $step3 = null;
    public ?CppCheckoutStep4 $step4 = null;

    public $paymentMethod = null;

    public array $items = [];

    public ?CppCheckout $checkout = null;

    public ?TypeGatewayEnum $typeGateway = null;

    public ?CppGateways $cppGateways = null;

    public function mount(?CppCheckout $cppCheckout = null): void
    {
        if (!Auth::check()) {
            filament()
                ->getCurrentPanel()
                ->topNavigation()
                ->topbar(false);
        }

        $this->cppGateways = CppGateways::where('active', true)->first();
        $this->checkout    = $cppCheckout;
        $this->typeGateway = config()->get('checkout-payment.type_gateway');

        $this->step1 = $this?->checkout?->step1()?->first();
        $this->items = $this->step1?->items
            ? json_decode($this?->checkout?->step1()?->first()?->items, true)
            : [];

        $this->step2 = $this?->checkout?->step2()?->first();
        $this->people_type  = $this->step2->people_type ?? null;
        $this->first_name   = $this->step2->first_name ?? null;
        $this->last_name    = $this->step2->last_name ?? null;
        $this->email        = $this->step2->email ?? null;
        $this->phone_number = $this->step2->phone_number ?? null;
        $this->document     = $this->step2->document ?? null;

        $this->step3 = $this?->checkout?->step3()?->first();
        $this->step4 = $this?->checkout?->step4()?->first();

        $this->form->fill();
    }

    public static function getSlug(): string
    {
        return 'internal-checkout-payment/{cppCheckout?}';
    }

    public function fieldWinzard()
    {
        return [
            Wizard\Step::make('Produtos do Carrinho')
                ->visible($this->step1->visible ?? true)
                ->schema([
                    \Filament\Forms\Components\View::make(
                        'checkout-payment::checkout.cart-products'
                    ),
                ]),
            Wizard\Step::make('Dados Pessoais')
                ->visible($this->step2->visible ?? true)
                ->schema([

                    Grid::make()->schema([

                        Select::make('people_type')
                            ->label("Física/Jurídica")
                            ->autofocus()
                            ->live()
                            ->default($this->step2->people_type ?? null)
                            ->options(
                                collect(TypePeopleEnum::cases())
                                    ->mapWithKeys(fn(TypePeopleEnum $type) => [
                                        $type->value => $type->label()
                                    ])->toArray()
                            )
                            ->required(),

                        TextInput::make('document')
                            ->label("CPF/CNPJ")
                            ->reactive()
                            ->placeholder(function (Get $get) {
                                $people_type = $get("people_type");
                                return $people_type == 2 ? "99.999.999/9999-99" : "999.999.999-99";
                            })
                            ->mask(function (Get $get) {
                                $people_type = $get("people_type");
                                return $people_type == 2 ? "99.999.999/9999-99" : "999.999.999-99";
                            })
                            ->maxLength(50)
                            ->required(),

                        TextInput::make('phone_number')
                            ->required()
                            ->label('Telefone/Celular')
                            ->default(fn($state, $get, $set, $livewire) => $livewire->phone_number),

                    ])->columns(3),

                    Grid::make()->schema([

                        TextInput::make('first_name')
                            ->required()
                            ->label('Primeiro Nome')
                            ->default(fn($state, $get, $set, $livewire) => $livewire->first_name),

                        TextInput::make('last_name')
                            ->required()
                            ->label('Sobrenome')
                            ->default(fn($state, $get, $set, $livewire) => $livewire->last_name),

                        TextInput::make('email')
                            ->required()
                            ->label('E-mail')
                            ->email()
                            ->default(fn($state, $get, $set, $livewire) => $livewire->email),

                    ])->columns(3),

                ]),
            Wizard\Step::make('Dados de Endereço')
                ->visible($this->step3->visible ?? true)
                ->schema([

                ]),
            Wizard\Step::make('Pagamento')
                ->visible($this->step4->visible ?? true)
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
        if (!$this->cppGateways) {
            return [
                View::make(
                    'checkout-payment::partials.no-gateway-message'
                ),
            ];
        }

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
        return config()->get('checkout-payment.sidebar_group');
    }
}
