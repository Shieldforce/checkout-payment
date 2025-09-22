<?php

namespace Shieldforce\CheckoutPayment\Resources;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum;
use Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum;
use Shieldforce\CheckoutPayment\Enums\TypeStepEnum;
use Shieldforce\CheckoutPayment\Models\CppCheckout;
use Shieldforce\CheckoutPayment\Models\CppCheckoutStep2;
use Shieldforce\CheckoutPayment\Pages\InternalCheckoutWizard;
use Shieldforce\CheckoutPayment\Resources\CppCheckoutResource\Pages\CreateCppCheckout;
use Shieldforce\CheckoutPayment\Resources\CppCheckoutResource\Pages\EditCppCheckout;
use Shieldforce\CheckoutPayment\Resources\CppCheckoutResource\Pages\ListCppCheckouts;

class CppCheckoutResource extends Resource
{
    protected static ?string $model           = CppCheckout::class;
    protected static ?string $navigationIcon  = 'heroicon-o-currency-dollar';
    protected static ?string $label           = "Cobrança";
    protected static ?string $pluralLabel     = "Cobranças";
    protected static ?string $navigationLabel = "Cobranças";
    protected static ?string $slug            = "checkouts-payment";

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
                /*TextColumn::make('referencable_id')
                    ->label('TRI')
                    ->description("Id de ref."),
                TextColumn::make('referencable_type')
                    ->label('TRT')
                    ->formatStateUsing(function ($state) {
                        return str_replace(["\\","App","Models"], ["","",""], $state);
                    })
                    ->description("Tipo de ref."),*/

                TextColumn::make('step2')
                    ->label('Cliente')
                    ->formatStateUsing(function ($record) {
                        $first = $record->step2()->first();
                        return $first ? $first->first_name . ' ' . $first->last_name : '-';
                    }),

                TextColumn::make('methods')
                    ->label('Métodos/Pag')
                    ->description('Métodos liberados')
                    ->formatStateUsing(function ($state) {
                        $array = json_decode($state, true);
                        $tags  = [];
                        foreach ($array as $key => $value) {
                            $tags[] = MethodPaymentEnum::from($value)->label();
                        }
                        return implode(', ', $tags);
                    })
                    ->html(),

                TextColumn::make('total_price')
                    ->label('Valor')
                    ->description("Valor da cobrança!")
                    ->formatStateUsing(function ($state) {
                        return "R$ ". number_format($state, 2, ",", ".");
                    }),

                TextColumn::make('due_date')
                    ->label('Vencimento')
                    ->formatStateUsing(function ($state) {
                        return Carbon::createFromFormat('Y-m-d', $state)
                            ->format('d/m/Y');
                    }),

                BadgeColumn::make('status')
                    ->formatStateUsing(fn($state, $record) => StatusCheckoutEnum::labelEnum($state))
                    ->color(fn($state, $record) => StatusCheckoutEnum::colorEnum($state))
                    ->label('Status')
                    ->sortable(),

                BadgeColumn::make('startOnStep')
                    ->formatStateUsing(fn($state, $record) => TypeStepEnum::from($state)->label())
                    ->color("success")
                    ->label('Passo Atual')
                    ->sortable(),

            ])
            ->filters([

                SelectFilter::make('document')
                    ->label('CPF/CNPJ')
                    ->searchable()
                    ->optionsLimit(5)
                    ->options(
                        CppCheckoutStep2::query()
                            ->whereNotNull('document')
                            ->select('document')
                            ->distinct()
                            ->orderBy('document')
                            ->pluck('document', 'document')
                            ->toArray()
                    )
                    ->query(function ($query, array $data) {
                        if (! empty($data['value'])) {
                            $query->whereHas('step2', function ($subQuery) use ($data) {
                                $subQuery->where('document', $data['value']);
                            });
                        }
                    }),

                SelectFilter::make('email')
                    ->label('Email')
                    ->searchable()
                    ->optionsLimit(5)
                    ->options(
                        CppCheckoutStep2::query()
                            ->whereNotNull('email')
                            ->select('email')
                            ->distinct()
                            ->orderBy('email')
                            ->pluck('email', 'email')
                            ->toArray()
                    )
                    ->query(function ($query, array $data) {
                        if (! empty($data['value'])) {
                            $query->whereHas('step2', function ($subQuery) use ($data) {
                                $subQuery->where('email', $data['value']);
                            });
                        }
                    }),

                SelectFilter::make('first_name')
                    ->label('Primeiro Nome')
                    ->searchable()
                    ->optionsLimit(5)
                    ->options(
                        CppCheckoutStep2::query()
                            ->whereNotNull('first_name')
                            ->select('first_name')
                            ->distinct()
                            ->orderBy('first_name')
                            ->pluck('first_name', 'first_name')
                            ->toArray()
                    )
                    ->query(function ($query, array $data) {
                        if (! empty($data['value'])) {
                            $query->whereHas('step2', function ($subQuery) use ($data) {
                                $subQuery->where('first_name', $data['value']);
                            });
                        }
                    }),

                Filter::make('vencimento')
                    ->columnSpan(2)
                    ->form([
                        DatePicker::make('due_date_start')
                            ->label('Vencimento (Inicial)'),
                        DatePicker::make('due_date_end')
                            ->label('Vencimento (Final)'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_date_start'],
                                function (Builder $query, $date): Builder {
                                    return $query->whereDate('due_date', '>=', $date);
                                }
                            )
                            ->when(
                                $data['due_date_end'],
                                function (Builder $query, $date): Builder {
                                    return $query->whereDate('due_date', '<=', $date);
                                }
                            );
                    })->columns(2),

            ], Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->actions([
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make("Link de Pagamento")
                        ->icon("heroicon-o-credit-card")
                        ->url(function (Model $record) {
                            return "/admin/checkout/{$record->uuid}";
                        })
                        ->openUrlInNewTab(),

                ])
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

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check();
    }
}
