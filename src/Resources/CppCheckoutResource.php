<?php

namespace Shieldforce\CheckoutPayment\Resources;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum;
use Shieldforce\CheckoutPayment\Models\CppCheckout;
use Shieldforce\CheckoutPayment\Resources\CppCheckoutResource\Pages\CreateCppCheckout;
use Shieldforce\CheckoutPayment\Resources\CppCheckoutResource\Pages\EditCppCheckout;
use Shieldforce\CheckoutPayment\Resources\CppCheckoutResource\Pages\ListCppCheckouts;

class CppCheckoutResource extends Resource
{
    protected static ?string $model           = CppCheckout::class;
    protected static ?string $navigationIcon  = 'heroicon-o-currency-dollar';
    protected static ?string $label           = "Checkout";
    protected static ?string $pluralLabel     = "Checkouts";
    protected static ?string $navigationLabel = "Checkouts";
    protected static ?string $slug            = "checkouts";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('referencable_id')
                    ->label('TRI')
                    ->description("Id de referência"),
                TextColumn::make('referencable_type')
                    ->label('TRT')
                    ->description("Tipo de referência"),

                TextColumn::make('methods')
                    ->label('Métodos/Pag')
                    ->description('Métodos de pagamentos liberados')
                    ->formatStateUsing(function ($state) {
                        $array = json_decode($state, true);
                        $tags = [];
                        foreach ($array as $key => $value) {
                            $tags[] = MethodPaymentEnum::from($value)->label();
                        }
                        return implode(', ', $tags);
                    })
                    ->html()

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCppCheckouts::route('/'),
            'create' => CreateCppCheckout::route('/create'),
            'edit'   => EditCppCheckout::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return config()->get('checkout-payment.sidebar_group');
    }
}
