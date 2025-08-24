<?php

namespace Shieldforce\CheckoutPayment\Services;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Crypt;
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
                    ->visible()[$nameField] : false;
            })
            ->extraInputAttributes(['x-ref' => "apiKeyInput-{$nameField}"])
            ->formatStateUsing(fn ($state) => $state ? Crypt::decrypt($state) : null)
            ->suffixAction(
                Action::make('copy')
                    ->icon('heroicon-m-clipboard')
                    ->tooltip('Copiar')
                    ->action(
                        fn ($state) => \Filament\Notifications\Notification::make()
                            ->title('Copiado!')
                            ->body('A chave foi copiada para a área de transferência.')
                            ->success()
                            ->send()
                    )
                    ->extraAttributes([
                        'x-on:click.stop' => "navigator.clipboard.writeText(\$refs.apiKeyInput-{$nameField}.value)",
                    ])
            );
    }
}
