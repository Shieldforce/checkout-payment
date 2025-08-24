<?php

namespace Shieldforce\CheckoutPayment\Services;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Tables\Columns\TextColumn;
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
            ->password(function (Get $get, $state) use ($nameField) {
                $name = $get('name');

                return $name ? TypeGatewayEnum::from($name)
                                   ->password()[$nameField] : false;
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
                                   ->visible()[$nameField] : true;
            });
    }

    public static function TextColumn($fieldName, $label)
    {
        return TextColumn::make($fieldName)
            ->label($label)
            ->limit(function ($state, $record) use ($fieldName) {
                return TypeGatewayEnum::from($record->name)->limit()[$fieldName];
            })
            ->tooltip(function ($state, $record) use ($fieldName) {
                return TypeGatewayEnum::from($record->name)->tooltip()[$fieldName];
            })
            ->description(function ($state, $record) use ($fieldName) {
                return TypeGatewayEnum::from($record->name)->description()[$fieldName];
            })
            ->visible(function ($state, $record) use ($fieldName) {
                return TypeGatewayEnum::from($record->name)->visible()[$fieldName];
            });
    }
}
