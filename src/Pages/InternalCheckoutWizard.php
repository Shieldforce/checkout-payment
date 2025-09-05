<?php

namespace Shieldforce\CheckoutPayment\Pages;

use Carbon\Carbon;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
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
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;
use Livewire\Component;
use Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum;
use Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum;
use Shieldforce\CheckoutPayment\Enums\TypeGatewayEnum;
use Shieldforce\CheckoutPayment\Enums\TypePeopleEnum;
use Shieldforce\CheckoutPayment\Jobs\ProcessBillingCreditCardJob;
use Shieldforce\CheckoutPayment\Models\CppCheckout;
use Shieldforce\CheckoutPayment\Models\CppCheckoutStep1;
use Shieldforce\CheckoutPayment\Models\CppCheckoutStep2;
use Shieldforce\CheckoutPayment\Models\CppCheckoutStep3;
use Shieldforce\CheckoutPayment\Models\CppCheckoutStep4;
use Shieldforce\CheckoutPayment\Models\CppGateways;
use Shieldforce\CheckoutPayment\Services\BuscarViaCepService;
use Shieldforce\CheckoutPayment\Services\MercadoPago\MercadoPagoService;
use Throwable;

class InternalCheckoutWizard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'checkout-payment::pages.internal-checkout-wizard';

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Pagamentos';

    protected static ?string $label = 'Checkout';

    protected static ?string $navigationLabel = 'Checkout';

    protected static ?string $title = 'Realizar Pagamento';

    public ?string $people_type = null;

    public ?string $first_name = null;

    public ?string $last_name = null;

    public ?string $email = null;

    public ?string $email_card = null;

    public ?string $phone_number = null;

    public ?string $document = null;

    public ?string $zipcode = null;

    public ?string $street = null;

    public ?string $district = null;

    public ?string $city = null;

    public ?string $state = null;

    public ?string $number = null;

    public ?string $complement = null;

    public ?string $card_number = null;

    public ?string $card_token = null;

    public ?string $card_validate = null;

    public ?string $card_cvv = null;

    public ?string $card_payer_name = null;

    public ?string $payment_method_id = null;

    public ?string $base_qrcode = null;

    public ?string $url_qrcode = null;

    public ?string $url_billet = null;

    public ?string $review = null;

    public ?CppCheckoutStep1 $step1 = null;

    public ?CppCheckoutStep2 $step2 = null;

    public ?CppCheckoutStep3 $step3 = null;

    public ?CppCheckoutStep4 $step4 = null;

    public ?CppCheckout $checkout = null;

    public ?TypeGatewayEnum $typeGateway = null;

    public ?CppGateways $cppGateways = null;

    public array $data = [];

    public array $items = [];

    public int $startOnStep = 1;

    public ?int $method_checked = null;

    public $installments = 1;

    public $issuer;

    public $statusCheckout;

    public $total_price;

    public ?bool $qrcode_yes;

    public ?array $attempts;

    public array $paymentMethods = [
        MethodPaymentEnum::debit_card,
        MethodPaymentEnum::pix,
        MethodPaymentEnum::billet,
    ];

    public function mount(?string $cppCheckoutUuid = null): void
    {
        if (!Auth::check()) {
            filament()
                ->getCurrentPanel()
                ->topNavigation()/*
                ->topbar(false)*/
            ;
        }

        $this->cppGateways = CppGateways::where('active', true)->first();
        $this->typeGateway = config()->get('checkout-payment.type_gateway');

        if ($cppCheckoutUuid) {
            $this->checkout = CppCheckout::where('uuid', $cppCheckoutUuid)->first();

            $this->attempts = json_decode($this->checkout->return_gateway ?? '[]', true);

            $this->method_checked = $this->checkout->method_checked ?? null;
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

            $this->step4             = $this?->checkout?->step4()?->first();
            $this->card_number       = $this->step4->card_number ?? null;
            $this->card_token        = $this->step4->card_token ?? null;
            $this->installments      = $this->step4->installments ?? null;
            $this->payment_method_id = $this->step4->payment_method_id ?? null;
            $this->card_validate     = $this->step4->card_validate ?? null;
            $this->card_payer_name   = $this->step4->card_payer_name ?? null;
            $this->base_qrcode       = $this->step4->base_qrcode ?? null;
            $this->url_qrcode        = $this->step4->url_qrcode ?? null;
            $this->url_billet        = $this->step4->url_billet ?? null;

            // mudar para true quando gerar o qrcode ---
            $this->qrcode_yes = false;

            if (isset($this->step1->id) && isset($this->step1->items)) {
                $items = json_decode($this->step1->items, true);
                $sum   = 0;
                foreach ($items as $item) {
                    $sum += $item['price'] * $item['quantity'];
                }

                $this->total_price = $sum;
            }

            $this->startOnStep = $this->checkout->startOnStep ?? null;

           /* if (isset($this->attempts) && count($this->attempts) > 0) {
                $this->checkout->update([
                    "startOnStep" => 5,
                ]);

                $this->startOnStep = 5;
            }*/

            $this->statusCheckout = $this->checkout->status ?? null;
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
                ->afterValidation(function (Get $get) {
                    $this->checkout->update([
                        'startOnStep' => 2,
                    ]);
                })
                ->schema([
                    \Filament\Forms\Components\View::make(
                        'checkout-payment::checkout.cart-products'
                    ),
                ]),
            Wizard\Step::make('Dados Pessoais')
                ->visible($this->step2->visible ?? true)
                ->disabled($this->checkout->startOnStep != 2)
                ->afterValidation(function (Get $get) {

                    $step2Update = $this->checkout->step2()->updateOrCreate(
                        ['cpp_checkout_id' => $this->checkout->id],
                        [
                            'people_type'  => $get('people_type'),
                            'document'     => $get('document'),
                            'phone_number' => $get('phone_number'),
                            'first_name'   => $get('first_name'),
                            'last_name'    => $get('last_name'),
                            'email'        => $get('email'),
                        ]
                    );

                    if ($step2Update) {
                        $this->checkout->update(['startOnStep' => 3]);
                    }

                    if (!$step2Update) {
                        throw new Halt;
                    }

                })
                ->schema([

                    Grid::make()->schema([

                        Select::make('people_type')
                            ->label('Física/Jurídica')
                            ->autofocus()
                            ->live()
                            ->default(fn($state, $get, $set, $livewire) => $livewire->people_type)
                            ->options(
                                collect(TypePeopleEnum::cases())
                                    ->mapWithKeys(fn(TypePeopleEnum $type) => [
                                        $type->value => $type->label(),
                                    ])->toArray()
                            )
                            ->afterStateUpdated(fn(Set $set) => $set('document', null))
                            ->required(),

                        TextInput::make('document')
                            ->label('CPF/CNPJ')
                            ->reactive()
                            ->default(fn($state, $get, $set, $livewire) => $livewire->document)
                            ->placeholder(function (Get $get) {
                                $people_type = $get('people_type');

                                return $people_type == 2 ? '99.999.999/9999-99' : '999.999.999-99';
                            })
                            ->mask(function (Get $get) {
                                $people_type = $get('people_type');

                                return $people_type == 2 ? '99.999.999/9999-99' : '999.999.999-99';
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
                            ->extraInputAttributes(['id' => 'email'])
                            ->label('E-mail')
                            ->email()
                            ->default(fn($state, $get, $set, $livewire) => $livewire->email),

                    ])->columns(3),

                ]),
            Wizard\Step::make('Dados de Endereço')
                ->visible($this->step3->visible ?? true)
                ->afterValidation(function (Get $get) {

                    $step3Update = $this->checkout->step3()->updateOrCreate(
                        ['cpp_checkout_id' => $this->checkout->id],
                        [
                            'zipcode'    => $get('zipcode'),
                            'street'     => $get('street'),
                            'number'     => $get('number'),
                            'district'   => $get('district'),
                            'city'       => $get('city'),
                            'state'      => $get('state'),
                            'complement' => $get('complement'),
                        ]
                    );

                    if ($step3Update) {
                        $this->checkout->update(['startOnStep' => 4]);
                    }

                    if (!$step3Update) {
                        throw new Halt;
                    }

                })
                ->schema([

                    Grid::make()->schema([

                        TextInput::make('zipcode')
                            ->label('CEP')
                            ->default(fn($state, $get, $set, $livewire) => $livewire->zipcode)
                            ->suffixAction(
                                Action::make('viaCep')
                                    ->label('Buscar CEP')
                                    ->icon('heroicon-m-map-pin')
                                    // ->requiresConfirmation()
                                    ->action(function (Set $set, $state, Get $get, Component $livewire) {
                                        $data = BuscarViaCepService::getData((string)$state);

                                        if (isset($data['cep'])) {
                                            $set('street', $data['logradouro']);
                                            $set('complement', $data['complemento']);
                                            $set('district', $data['bairro']);
                                            $set('city', $data['localidade']);
                                            $set('state', $data['uf']);
                                        }
                                    })
                            )
                            ->hint('Busca de CEP')
                            ->afterStateUpdated(function (Set $set, Get $get, Component $livewire) {
                                $data = BuscarViaCepService::getData((string)$get('zipcode'));

                                if (isset($data['cep'])) {
                                    $set('street', $data['logradouro']);
                                    $set('complement', $data['complemento']);
                                    $set('district', $data['bairro']);
                                    $set('city', $data['localidade']);
                                    $set('state', $data['uf']);
                                }
                            })
                            ->mask(function (Get $get) {
                                return '99999-999';
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
                ->afterValidation(function (Get $get) {

                    try {
                        $step4Update = $this->checkout->step4()->updateOrCreate(
                            ['cpp_checkout_id' => $this->checkout->id],
                            [
                                'card_number'       => str_replace(' ', '', $get('card_number')),
                                'card_validate'     => $get('card_validate'),
                                'card_payer_name'   => $get('card_payer_name'),
                                'card_token'        => $get('card_token'),
                                'installments'      => $get('installments'),
                                'payment_method_id' => $get('payment_method_id'),
                                'base_qrcode'       => $get('base_qrcode'),
                                'url_qrcode'        => $get('url_qrcode'),
                                'url_billet'        => $get('url_billet'),
                            ]
                        );

                        if ($step4Update) {
                            $this->checkout->update([
                                'startOnStep'    => 5,
                                'status'         => StatusCheckoutEnum::pendente->value,
                                'method_checked' => $get('method_checked'),
                            ]);

                            if ($this->checkout->step4->first()) {
                                ProcessBillingCreditCardJob::dispatch($step4Update)->delay(60);
                            }
                        }
                    } catch (Halt $exception) {
                        throw $exception;
                    }

                })
                ->schema([
                    Select::make('method_checked')
                        //->default(fn($state, $get, $set, $livewire) => $livewire->method_checked)
                        ->extraAttributes(['id' => 'method_checked'])
                        ->label('Escolha como quer pagar!')
                        ->live()
                        ->options(
                            collect($this->paymentMethods)
                                ->mapWithKeys(fn(MethodPaymentEnum $method) => [
                                    $method->value => $method->label(),
                                ])
                                ->toArray()
                        )
                        ->required(),

                    Grid::make(2)->schema([

                        // Preview do cartão
                        Grid::make()->schema([

                            View::make('checkout-payment::checkout.card-preview')
                                ->visible(fn(Get $get) => $get('method_checked') === MethodPaymentEnum::credit_card->value)
                                ->columnSpanFull(),

                            Hidden::make('card_token')
                                ->id('cardToken'),

                            Hidden::make('payment_method_id')
                                ->id('paymentMethodId'),

                        ])->columnSpan(1),

                        Grid::make()->schema([

                            Grid::make()->schema([

                                Select::make('installments')
                                    ->label('Quantidade de parcelas')
                                    ->extraInputAttributes(['id' => 'installments']),

                                Select::make('issuer')
                                    ->label('Tipo de cartão')
                                    ->disabled()
                                    ->dehydrated()
                                    ->extraInputAttributes(['id' => 'issuer']),

                            ])->columns(2),

                            // Card method ---
                            TextInput::make('card_number')
                                ->label('Número do Cartão')
                                ->extraInputAttributes([
                                    'id'    => 'cardNumber',
                                    'class' => 'cc_number',
                                ])
                                // ->reactive()
                                ->mask(function ($state, $get, $set, $livewire) {
                                    return '9999 9999 9999 9999 99';
                                })
                                // ->maxLength(19)
                                ->required(function ($state, $get, $set, $livewire) {
                                    return $get('method_checked')
                                        ? $get('method_checked') == MethodPaymentEnum::credit_card->value
                                        : false;
                                })
                                ->visible(function ($state, $get, $set, $livewire) {
                                    return $get('method_checked')
                                        ? $get('method_checked') == MethodPaymentEnum::credit_card->value
                                        : false;
                                })
                                ->disabled(function ($state, $get, $set, $livewire) {
                                    return $get('method_checked')
                                    && $get('method_checked') == MethodPaymentEnum::credit_card->value
                                        ? false
                                        : true;
                                }),
                            TextInput::make('card_validate')
                                ->label('Validade do Cartão')
                                ->extraInputAttributes(['id' => 'cardExpiration'])
                                // ->reactive()
                                // ->mask('99/99')
                                ->required(function ($state, $get, $set, $livewire) {
                                    return $get('method_checked')
                                        ? $get('method_checked') == MethodPaymentEnum::credit_card->value
                                        : false;
                                })
                                ->visible(function ($state, $get, $set, $livewire) {
                                    return $get('method_checked')
                                        ? $get('method_checked') == MethodPaymentEnum::credit_card->value
                                        : false;
                                })
                                ->disabled(function ($state, $get, $set, $livewire) {
                                    return $get('method_checked')
                                    && $get('method_checked') == MethodPaymentEnum::credit_card->value
                                        ? false
                                        : true;
                                }),
                            TextInput::make('card_payer_name')
                                ->label('Nome impresso no Cartão')
                                ->extraInputAttributes(['id' => 'cardholderName'])
                                // ->reactive()
                                ->required(function ($state, $get, $set, $livewire) {
                                    return $get('method_checked')
                                        ? $get('method_checked') == MethodPaymentEnum::credit_card->value
                                        : false;
                                })
                                ->visible(function ($state, $get, $set, $livewire) {
                                    return $get('method_checked')
                                        ? $get('method_checked') == MethodPaymentEnum::credit_card->value
                                        : false;
                                })
                                ->disabled(function ($state, $get, $set, $livewire) {
                                    return $get('method_checked')
                                    && $get('method_checked') == MethodPaymentEnum::credit_card->value
                                        ? false
                                        : true;
                                }),
                            TextInput::make('card_cvv')
                                ->label('CVV do Cartão')
                                ->extraInputAttributes(['id' => 'cardCVV'])
                                // ->reactive()
                                ->required(function ($state, $get, $set, $livewire) {
                                    return $get('method_checked')
                                        ? $get('method_checked') == MethodPaymentEnum::credit_card->value
                                        : false;
                                })
                                ->visible(function ($state, $get, $set, $livewire) {
                                    return $get('method_checked')
                                        ? $get('method_checked') == MethodPaymentEnum::credit_card->value
                                        : false;
                                })
                                ->disabled(function ($state, $get, $set, $livewire) {
                                    return $get('method_checked')
                                    && $get('method_checked') == MethodPaymentEnum::credit_card->value
                                        ? false
                                        : true;
                                }),

                        ])->columns(2)->columnSpan(1),

                    ])->visible(fn(Get $get) => $get('method_checked') === MethodPaymentEnum::credit_card->value),

                    Grid::make(2)->schema([

                        // Preview do Pix
                        View::make('checkout-payment::checkout.pix-preview')
                            ->visible(fn(Get $get) => $get('method_checked') === MethodPaymentEnum::pix->value)
                            ->columnSpanFull(),

                        // Pix method ---
                        Hidden::make('base_qrcode')
                            ->default($this->base_qrcode ?? $this->step4->base_qrcode ?? null),
                        Hidden::make('url_qrcode')
                            ->default($this->url_qrcode ?? $this->step4->url_qrcode ?? null),

                    ])->visible(fn(Get $get) => $get('method_checked') === MethodPaymentEnum::pix->value),


                    Grid::make(2)->schema([

                        // Preview do Billet
                        View::make('checkout-payment::checkout.billet-preview')
                            ->visible(fn(Get $get) => $get('method_checked') === MethodPaymentEnum::billet->value)
                            ->columnSpanFull(),

                        // Billet method ---
                        Hidden::make('url_billet')
                            ->default($this->url_billet ?? $this->step4->url_billet ?? null),

                    ])->visible(fn(Get $get) => $get('method_checked') === MethodPaymentEnum::billet->value),

                ]),
            Wizard\Step::make('Checkout Finalizado')
                ->schema([
                    View::make('checkout-payment::checkout.status-badge')
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

        $submitAction = new HtmlString(
            Blade::render('
                <x-filament::button
                    wire:click="submit"
                    type="button"
                    color="{{ $color }}"
                    icon="heroicon-s-check"
                >
                    {{ $text }}
                </x-filament::button>
            ', [
                'color' => $this->checkout->color_button_submit,
                'text'  => $this->checkout->text_button_submit,
            ])
        );

        if (!isset($this->checkout->url)) {
            $submitAction = view('checkout-payment::checkout.submit-button-hidden');
        }

        return [
            Wizard::make($this->fieldWinzard())
                ->submitAction($submitAction)
                ->nextAction(
                    fn(Action $action) => $action
                        ->label('Próximo')
                        ->icon('heroicon-s-arrow-right')
                        ->visible(fn() => $this->checkout->startOnStep != 5)
                        ->disabled(fn() => $this->checkout->startOnStep == 5)
                        ->extraAttributes([
                            'id' => 'btn-next-step',
                        ])
                )
                ->previousAction(
                    fn(Action $action) => $action
                        ->label('Voltar')
                        ->visible(fn() => $this->checkout->startOnStep != 5)
                        ->disabled(fn() => $this->checkout->startOnStep == 5)
                        ->icon('heroicon-s-arrow-left')
                )
                ->startOnStep($this->startOnStep),
        ];
    }

    public function submit()
    {
        //dd($this->form->getState());
        return redirect($this->checkout->url);
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

    protected $listeners = [
        'showNotification'      => 'showNotification',
        'goToStep'              => 'goToStep',
        'updateCardToken'       => 'updateCardToken',
        'paymentMethodId'       => 'paymentMethodId',
        'goInstallments'        => 'goInstallments',
        'refreshStatusCheckout' => 'refreshStatusCheckout',
        'methodCheckedChange'   => 'methodCheckedChange',
        'chooseOtherMethod'     => 'chooseOtherMethod',
    ];

    #[On('show-notification')]
    public function showNotification(
        string $title = 'Aviso',
        string $body = '',
        string $status = 'info'
    ): void
    {
        \Filament\Notifications\Notification::make()
            ->title($title ?? 'titulo')
            ->body($body ?? 'corpo')
            ->seconds(30)
            ->{$status ?? 'success'}()
            ->send();
    }

    #[On('go-to-step')]
    public function goToStep(
        $step,
    ): void
    {
        if ($step) {
            $this->startOnStep = $step;
        }
    }

    #[On('update-card-token')]
    public function updateCardToken(
        $cardToken,
    ): void
    {
        if ($cardToken) {
            $this->card_token = $cardToken ?? null;
        }
    }

    #[On('payment-method-id')]
    public function paymentMethodId(
        $paymentMethodId,
    ): void
    {
        if ($paymentMethodId) {
            $this->payment_method_id = $paymentMethodId ?? null;
        }
    }

    #[On('go-installments')]
    public function goInstallments(
        $installments,
    ): void
    {
        if ($installments) {
            $this->installments = $installments ?? null;
        }
    }

    #[On('refresh-status-checkout')]
    public function refreshStatusCheckout(): void
    {
        $this->statusCheckout = $this->checkout->status;
    }

    #[On('method-checked-change')]
    public function methodCheckedChange($method): void
    {
        DB::beginTransaction();

        try {

            if (
                isset($this->step4->base_qrcode) &&
                $method == MethodPaymentEnum::pix->value
            ) {
                $this->base_qrcode = $this->step4->base_qrcode;
                $this->url_qrcode  = $this->step4->url_qrcode;
                $this->checkout->update([
                    "startOnStep" => 5,
                ]);
                return;
            }

            if (
                isset($this->step4->url_billet) &&
                $method == MethodPaymentEnum::billet->value
            ) {
                $this->url_billet = $this->step4->url_billet;
                $this->checkout->update([
                    "startOnStep" => 5,
                ]);
                return;
            }

            $mp                 = new MercadoPagoService();
            $step2              = $this->checkout?->step2()?->first();
            $step3              = $this->checkout?->step3()?->first();
            $date_of_expiration = Carbon::createFromFormat("Y-m-d", $this->checkout->due_date)
                    ->format("Y-m-d\TH:i:s") . ".000-04:00";

            $data = [
                "value"            => (float)$this->total_price ?? null,
                "external_id"      => $this->checkout->id ?? null,
                "payer_email"      => $step2->email ?? null,
                "payer_first_name" => $step2->first_name ?? null,
                "payer_last_name"  => $step2->last_name ?? null,
                "due_date"         => $date_of_expiration,
                "document"         => $step2->document,
                "document_type"    => TypePeopleEnum::from($step2->people_type)->mpLabel(),
                "address"          => [
                    "zip_code"      => $step3->zipcode ?? null,
                    "city"          => $step3->city ?? null,
                    "street_name"   => $step3->street ?? null,
                    "street_number" => $step3->number ?? null,
                    "neighborhood"  => $step3->district ?? null,
                    "federal_unit"  => $step3->state ?? null,
                ]
            ];

            if ($method == MethodPaymentEnum::pix->value && isset($data["value"])) {

                $return = $mp->gerarPagamentoPix(
                    value: $data["value"],
                    description: "Pagamento via Pix",
                    external_id: $data["external_id"],
                    payer_email: $data["payer_email"],
                    payer_first_name: $data["payer_first_name"],
                );

                logger($return);

                if (isset($return["qr_code_base64"])) {

                    $this->checkout->step4()->updateOrCreate([
                        "cpp_checkout_id" => $this->checkout->id,
                    ], [
                        "base_qrcode"       => $return["qr_code_base64"],
                        "url_qrcode"        => $return["qr_code"],
                        "request_pix_data"  => json_encode($data),
                        "response_pix_data" => json_encode($return),
                        'payment_method_id' => MethodPaymentEnum::pix->value,
                    ]);

                    $this->checkout->update([
                        "status"      => StatusCheckoutEnum::pendente->value,
                        "startOnStep" => 5,
                    ]);

                    $this->base_qrcode = $return["qr_code_base64"];
                    $this->url_qrcode  = $return["qr_code"];

                    DB::commit();
                }

                if (!isset($return["qr_code_base64"])) {
                    $this->checkout->step4()->updateOrCreate([
                        "cpp_checkout_id" => $this->checkout->id,
                    ], [
                        "request_pix_data"  => json_encode($data),
                        "response_pix_data" => json_encode($return),
                    ]);
                }
            }

            if ($method == MethodPaymentEnum::billet->value) {

                $return = $mp->gerarPagamentoBoleto(
                    value: $data["value"],
                    description: "Pagamento via Boleto",
                    external_id: $data["external_id"],
                    payer_email: $data["payer_email"],
                    payer_first_name: $data["payer_first_name"],
                    payer_last_name: $data["payer_last_name"],
                    due_date: $data["due_date"],
                    document: $data["document"],
                    document_type: $data["document_type"],
                    address: $data["address"]
                );

                logger($return);

                if (
                    isset($return["data"]["transaction_details"]["external_resource_url"]) ||
                    isset($return["pdf"])
                ) {

                    $pdf = $return["data"]["transaction_details"]["external_resource_url"] ?? $return["pdf"];

                    $this->checkout->step4()->updateOrCreate([
                        "cpp_checkout_id" => $this->checkout->id,
                    ], [
                        "url_billet"           => $pdf,
                        "request_billet_data"  => json_encode($data),
                        "response_billet_data" => json_encode($return),
                        'payment_method_id'    => MethodPaymentEnum::billet->value,
                    ]);

                    $this->checkout->update([
                        "status"      => StatusCheckoutEnum::pendente->value,
                        "startOnStep" => 5,
                    ]);

                    $this->url_billet = $return["pdf"];

                    DB::commit();

                }

                if (!isset($return["transaction_details"]["external_resource_url"])) {
                    $this->checkout->step4()->updateOrCreate([
                        "cpp_checkout_id" => $this->checkout->id,
                    ], [
                        "request_billet_data"  => json_encode($data),
                        "response_billet_data" => json_encode($return),
                    ]);
                }
            }

        } catch (Throwable $e) {
            DB::rollBack();

            logger($e->getMessage());

            $this->checkout->update([
                "method_checked" => null,
            ]);
        }
    }

    #[On('choose-other-method')]
    public function chooseOtherMethod(): void
    {
        $this->checkout->update([
            'startOnStep' => 4,
        ]);

        $this->startOnStep = 4;

        redirect("/admin/checkout/{$this->checkout->uuid}");
    }
}
