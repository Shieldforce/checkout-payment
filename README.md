# Plugin para filament checkout-payment

[![Latest Version on Packagist](https://img.shields.io/packagist/v/shieldforce/checkout-payment.svg?style=flat-square)](https://packagist.org/packages/shieldforce/checkout-payment)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/shieldforce/checkout-payment/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/shieldforce/checkout-payment/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/shieldforce/checkout-payment/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/shieldforce/checkout-payment/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/shieldforce/checkout-payment.svg?style=flat-square)](https://packagist.org/packages/shieldforce/checkout-payment)

Este plugin implementa checkout de pagamento interno e externo para o filament!

## Instalação

Instalar Via Composer:

```bash
composer require shieldforce/checkout-payment
```

Você precisa publicar as migrações:

```bash
php artisan vendor:publish --tag="checkout-payment-migrations"
php artisan migrate
```

Caso se arrependa (CUIDADO, TENHA CERTEZA QUE OS MIGRATIONS DO CHECKOUT FORAM OS ÚLTIMOS A SEREM RODADOS):
```bash
 php artisan migrate:rollback --step=3
```

Você precisa publicar as configurações:

```bash
php artisan vendor:publish --tag="checkout-payment-config"
```

Opcionalmente você pode publicar as views:

```bash
php artisan vendor:publish --tag="checkout-payment-views"
```

Conteúdo do arquivo de configuração ao publicar será parecido com este:

```php
return [
    'sidebar_group' => 'Checkout Payment',
    'type_gateway'  => \Shieldforce\CheckoutPayment\Enums\TypeGatewayEnum::mercado_pago,
];
```

## Usage

```php
$checkoutPayment = new Shieldforce\CheckoutPayment();
echo $checkoutPayment->echoPhrase('Hello, Shieldforce!');
```

# Exemplo de implementação completa:
```php

    /*
     * Neste exemplo você irá passar todos os dados do checkout já prontos,
     * com exceção do passo Pagamentos, que é de escolha exclusiva do cliente!
    */

    // Dados que irão alimentar o checkout ---
    $client     = $model->order->client;
    $partName   = explode(" ", trim($client->name));
    $fullPhone  = $client?->contacts()?->first()?->prefix . $client?->contacts()?->first()?->number;
    $address    = $client?->addresses()->where("main", 1)?->first();
    $typePeople = isset($client->document) && strlen($client->document) > 11
        ? TypePeopleEnum::J
        : TypePeopleEnum::F;

    // Criando Checkout ---
    $mountCheckout = new MountCheckoutStepsService(
        model: $model,
        requiredMethods: [
            MethodPaymentEnum::credit_card->value,
            MethodPaymentEnum::pix->value,
            MethodPaymentEnum::billet->value,
        ]
    );

    $mountCheckout->handle()
        // Configurando botão que finaliza o wizard ---
        ->configureButtonSubmit(
                text: "Dashboard",
                color: "info",
                urlRedirect: route("filament.admin.pages.dashboard"),
        )
        // Cadastrando step 1 ---
        ->step1(
            items: array_map(callback: function ($product) {
                return (new DtoStep1(
                    name: $product["name"],
                    price: $product["pivot"]["price"],
                    price_2: $product["pivot"]["price"],
                    price_3: $product["pivot"]["price"],
                    description: "Venda de produto: " . $product["name"],
                    img: $product["picture"],
                    quantity: $product["pivot"]["quantity"],
                ))->toArray();
            }, array: $model->order->products->toArray()),
            visible: true,
        )
        // Cadastrando step 2 ---
        ->step2(
            data: new DtoStep2(
                people_type: $typePeople,
                first_name: $partName[0],
                last_name: $partName[1] ?? "Não Informado",
                email: $client->email,
                phone_number: $fullPhone,
                document: $client->document,
                visible: true,
            )
        )
        // Cadastrando step 3 ---
        ->step3(
            data: new DtoStep3(
                zipcode: $address->zipcode,
                street: $address->street,
                district: $address->district,
                city: $address->city,
                state: $address->state,
                number: $address->number,
                complement: $address->complement,
                visible: true,
            )
        );
```

# Exemplo de implementação simples:
```php
    
    /*
     * Neste exemplo você irá passar somente os itens do carrinho,
     * e o cliente irá informar todos os passos.
    */
    
    // Criando Checkout ---
    $mountCheckout = new MountCheckoutStepsService(
        model: $model,
        requiredMethods: [
            MethodPaymentEnum::credit_card->value,
            MethodPaymentEnum::pix->value,
            MethodPaymentEnum::billet->value,
        ]
    );

    $mountCheckout->handle()
        // Configurando botão que finaliza o wizard ---
        ->configureButtonSubmit(
                text: "Dashboard",
                color: "info",
                urlRedirect: route("filament.admin.pages.dashboard"),
        )
        // Cadastrando step 1 ---
        ->step1(
            items: array_map(callback: function ($product) {
                return (new DtoStep1(
                    name: $product["name"],
                    price: $product["pivot"]["price"],
                    price_2: $product["pivot"]["price"],
                    price_3: $product["pivot"]["price"],
                    description: "Venda de produto: " . $product["name"],
                    img: $product["picture"],
                    quantity: $product["pivot"]["quantity"],
                ))->toArray();
            }, array: $model->order->products->toArray()),
            visible: true,
        );
```

## Testing

```bash
composer test
```

## Changelog

Consulte [CHANGELOG](CHANGELOG.md) para obter mais informações sobre o que mudou recentemente.

## Contributing

Consulte [CONTRIBUTING](.github/CONTRIBUTING.md) para obter detalhes.

## Security Vulnerabilities

Revise [nossa política de segurança](../../security/policy) sobre como relatar vulnerabilidades de segurança.

## Credits

- [Alexandre Ferreira](https://github.com/Shieldforce)
- [All Contributors](../../contributors)

## License

A Licença do MIT (MIT). Consulte [Arquivo de Licença](LICENSE.md) para obter mais informações.
