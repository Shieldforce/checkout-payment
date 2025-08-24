<?php

namespace Shieldforce\CheckoutPayment\Services;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Shieldforce\CheckoutPayment\Enums\TypeGatewayEnum;

class ManagerFieldService
{
    public static function TextInput($nameField)
    {

        return TextInput::make($nameField)
            ->label(function (Get $get, $state) use ($nameField) {
                $name = $get('name');

                return $name ? TypeGatewayEnum::from($name)
                    ->labelFields()[$nameField] : 'Campo 1';
            })
            ->reactive()
            ->required(function (Get $get, $state) use ($nameField) {
                $name = $get('name');

                return $name ? TypeGatewayEnum::from($name)
                    ->required()[$nameField] : false;
            })
            ->visible(function (Get $get, $state) use ($nameField) {
                $name = $get('name');

                return $name ? TypeGatewayEnum::from($name)
                                   ->visible()[$nameField] : false;
            });
    }
}
