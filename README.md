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

Caso se arrependa:
```bash
 php artisan migrate:rollback --step=2
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
  "index" => "value"
];
```

## Usage

```php
$checkoutPayment = new Shieldforce\CheckoutPayment();
echo $checkoutPayment->echoPhrase('Hello, Shieldforce!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Alexandre Ferreira](https://github.com/Shieldforce)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
