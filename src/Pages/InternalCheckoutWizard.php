<?php

namespace Shieldforce\CheckoutPayment\Pages;

use Filament\Forms\Components\Actions\Action;
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
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum;
use Shieldforce\CheckoutPayment\Enums\TypeGatewayEnum;
use Shieldforce\CheckoutPayment\Enums\TypePeopleEnum;
use Shieldforce\CheckoutPayment\Models\CppCheckout;
use Shieldforce\CheckoutPayment\Models\CppCheckoutStep1;
use Shieldforce\CheckoutPayment\Models\CppCheckoutStep2;
use Shieldforce\CheckoutPayment\Models\CppCheckoutStep3;
use Shieldforce\CheckoutPayment\Models\CppCheckoutStep4;
use Shieldforce\CheckoutPayment\Models\CppGateways;
use Shieldforce\CheckoutPayment\Services\BuscarViaCepService;

class InternalCheckoutWizard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string  $view            = 'checkout-payment::pages.internal-checkout-wizard';
    protected static ?string $navigationIcon  = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Pagamentos';
    protected static ?string $label           = 'Checkout';
    protected static ?string $navigationLabel = 'Checkout';
    protected static ?string $title           = 'Realizar Pagamento';
    public ?string           $people_type     = null;
    public ?string           $first_name      = null;
    public ?string           $last_name       = null;
    public ?string           $email           = null;
    public ?string           $phone_number    = null;
    public ?string           $document        = null;
    public ?string           $zipcode         = null;
    public ?string           $street          = null;
    public ?string           $district        = null;
    public ?string           $city            = null;
    public ?string           $state           = null;
    public ?string           $number          = null;
    public ?string           $complement      = null;
    public ?CppCheckoutStep1 $step1           = null;
    public ?CppCheckoutStep2 $step2           = null;
    public ?CppCheckoutStep3 $step3           = null;
    public ?CppCheckoutStep4 $step4           = null;
    public ?CppCheckout      $checkout        = null;
    public ?TypeGatewayEnum  $typeGateway     = null;
    public ?CppGateways      $cppGateways     = null;
    public array             $data            = [];
    public array             $items           = [];
    public int               $startOnStep     = 1;
    public ?int              $method_checked  = null;
    public array             $paymentMethods  = [
        MethodPaymentEnum::debit_card,
        MethodPaymentEnum::pix,
        MethodPaymentEnum::billet,
    ];

    public function mount(?string $cppCheckoutUuid = null): void
    {
        if (!Auth::check()) {
            filament()
                ->getCurrentPanel()
                ->topNavigation()
                ->topbar(false);
        }

        $this->cppGateways = CppGateways::where('active', true)->first();
        $this->typeGateway = config()->get('checkout-payment.type_gateway');

        if ($cppCheckoutUuid) {
            $this->checkout = CppCheckout::where("uuid", $cppCheckoutUuid)->first();

            $this->method_checked = $this->checkout->method_checked;
            $this->paymentMethods = $this?->checkout?->methods
                ? array_map(function ($method) {
                    return MethodPaymentEnum::from($method);
                }, json_decode($this->checkout->methods, true))
                : $this->paymentMethods;

            // Step 1 --
            $this->step1 = $this?->checkout?->step1()?->first();
            $this->items = $this->step1?->items
                ? json_decode($this?->checkout?->step1()?->first()?->items, true)
                : [];

            // Step 2 ---
            $this->step2        = $this?->checkout?->step2()?->first();
            $this->people_type  = $this->step2->people_type ?? null;
            $this->first_name   = $this->step2->first_name ?? null;
            $this->last_name    = $this->step2->last_name ?? null;
            $this->email        = $this->step2->email ?? null;
            $this->phone_number = $this->step2->phone_number ?? null;
            $this->document     = $this->step2->document ?? null;

            // Step 3 ---
            $this->step3      = $this?->checkout?->step3()?->first();
            $this->zipcode    = $this->step3->zipcode ?? null;
            $this->street     = $this->step3->street ?? null;
            $this->district   = $this->step3->district ?? null;
            $this->city       = $this->step3->city ?? null;
            $this->state      = $this->step3->state ?? null;
            $this->number     = $this->step3->number ?? null;
            $this->complement = $this->step3->complement ?? null;

            $this->step4 = $this?->checkout?->step4()?->first();

            $this->startOnStep = $this->checkout->startOnStep ?? null;
        }

        $this->form->fill();
    }

    public static function getSlug(): string
    {
        return 'internal-checkout-payment/{cppCheckoutUuid?}';
    }

    public function fieldWinzard()
    {
        return [
            Wizard\Step::make('Carrinho')
                ->visible($this->step1->visible ?? true)
                ->schema([
                    \Filament\Forms\Components\View::make(
                        'checkout-payment::checkout.cart-products'
                    ),
                ]),
            Wizard\Step::make('Dados Pessoais')
                ->visible($this->step2->visible ?? true)
                ->afterValidation(function (Get $get) {

                    $step2Update = $this->checkout->step2()->updateOrCreate(["ccp_checkout_id" => $this->checkout->id], [
                        "people_type"  => $get("people_type"),
                        "document"     => $get("document"),
                        "phone_number" => $get("phone_number"),
                        "first_name"   => $get("first_name"),
                        "last_name"    => $get("last_name"),
                        "email"        => $get("email"),
                    ]);

                    if (!$step2Update) {
                        throw new Halt();
                    }

                })
                ->schema([

                    Grid::make()->schema([

                        Select::make('people_type')
                            ->label("Física/Jurídica")
                            ->autofocus()
                            ->live()
                            ->default(fn($state, $get, $set, $livewire) => $livewire->people_type)
                            ->options(
                                collect(TypePeopleEnum::cases())
                                    ->mapWithKeys(fn(TypePeopleEnum $type) => [
                                        $type->value => $type->label()
                                    ])->toArray()
                            )
                            ->afterStateUpdated(fn(Set $set) => $set("document", null))
                            ->required(),

                        TextInput::make('document')
                            ->label("CPF/CNPJ")
                            ->reactive()
                            ->default(fn($state, $get, $set, $livewire) => $livewire->document)
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

                    Grid::make()->schema([

                        TextInput::make('zipcode')
                            ->label("CEP")
                            ->default(fn($state, $get, $set, $livewire) => $livewire->zipcode)
                            ->suffixAction(
                                Action::make('viaCep')
                                    ->label("Buscar CEP")
                                    ->icon('heroicon-m-map-pin')
                                    //->requiresConfirmation()
                                    ->action(function (Set $set, $state, Get $get, Component $livewire) {
                                        $data = BuscarViaCepService::getData((string)$state);

                                        if (isset($data["cep"])) {
                                            $set('street', $data["logradouro"]);
                                            $set('complement', $data["complemento"]);
                                            $set('district', $data["bairro"]);
                                            $set('city', $data["localidade"]);
                                            $set('state', $data["uf"]);
                                        }
                                    })
                            )
                            ->hint("Busca de CEP")
                            ->afterStateUpdated(function (Set $set, Get $get, Component $livewire) {
                                $data = BuscarViaCepService::getData((string)$get("zipcode"));

                                if (isset($data["cep"])) {
                                    $set('street', $data["logradouro"]);
                                    $set('complement', $data["complemento"]);
                                    $set('district', $data["bairro"]);
                                    $set('city', $data["localidade"]);
                                    $set('state', $data["uf"]);
                                }
                            })
                            ->mask(function (Get $get) {
                                return "99999-999";
                            })
                            ->debounce(1000)
                            ->required(),

                        TextInput::make('street')
                            ->default(fn($state, $get, $set, $livewire) => $livewire->street)
                            ->label('Logradouro')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('number')
                            ->default(fn($state, $get, $set, $livewire) => $livewire->number)
                            ->label('Número')
                            ->maxLength(20),

                    ])->columns(3),

                    Grid::make()->schema([

                        TextInput::make('district')
                            ->default(fn($state, $get, $set, $livewire) => $livewire->district)
                            ->label('Bairro')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('city')
                            ->default(fn($state, $get, $set, $livewire) => $livewire->city)
                            ->label('Cidade')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('state')
                            ->default(fn($state, $get, $set, $livewire) => $livewire->state)
                            ->label('UF')
                            ->required()
                            ->maxLength(2),

                    ])->columns(3),

                    TextInput::make('complement')
                        ->default(fn($state, $get, $set, $livewire) => $livewire->complement)
                        ->label('Complemento')
                        ->maxLength(255)
                        ->columnSpanFull(),

                ]),
            Wizard\Step::make('Pagamento')
                ->visible($this->step4->visible ?? true)
                ->schema([
                    Select::make('method_checked')
                        ->default(fn($state, $get, $set, $livewire) => $livewire->method_checked)
                        ->label("Escolha como quer pagar!")
                        ->options(collect($this->paymentMethods)
                            ->mapWithKeys(fn(MethodPaymentEnum $method) => [
                                $method->value => $method->label(),
                            ])
                            ->toArray()
                        )
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
            Wizard::make($this->fieldWinzard())
                ->startOnStep($this->startOnStep),
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
