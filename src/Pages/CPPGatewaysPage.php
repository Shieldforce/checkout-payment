<?php

namespace Shieldforce\CheckoutPayment\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Shieldforce\CheckoutPayment\Models\CppGateways;
use Filament\Actions\Action as ActionAction;

class CPPGatewaysPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $view = 'checkout-payment::pages.cpp_gateways';

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Gateways';

    protected static ?string $label = 'Gateway';

    protected static ?string $navigationLabel = 'Gateway';

    protected static ?string $slug = 'cpp-gateways';

    protected static ?string $title = 'Lista de Gateways';

    public function mount(?int $checkoutId = null): void {}

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters(), layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(3)
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Filtrar...'),
            )
            ->bulkActions($this->getTableBulkActions())
            ->actions($this->getTableActions())
            ->headerActions($this->getHeaderActions());
    }

    public static function getNavigationGroup(): ?string
    {
        return config()->get('checkout-payment.sidebar_group');
    }

    public $record;

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return CppGateways::query();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')->sortable(),
            TextColumn::make('name'),
            TextColumn::make('field_1'),
            TextColumn::make('field_2'),
            TextColumn::make('field_3'),
            TextColumn::make('field_4'),
            TextColumn::make('field_5'),
            TextColumn::make('field_6'),
            ToggleColumn::make('active'),
            TextColumn::make('created_at')->dateTime(),
        ];
    }

    protected function getTableFilters(): array
    {
        $n = [
            SelectFilter::make('status')
                ->options([
                    'pending'   => 'Pending',
                    'paid'      => 'Paid',
                    'cancelled' => 'Cancelled',
                ]),
        ];

        return $n;
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionAction::make("create")
                ->label('Adicionar')
                ->form([
                    TextInput::make('name')->required(),
                    TextInput::make('field_1')->required(),
                    TextInput::make('field_2')->required(),
                    TextInput::make('field_3')->required(),
                    TextInput::make('field_4')->required(),
                    TextInput::make('field_5')->required(),
                    TextInput::make('field_6')->required(),
                    Toggle::make('active')->required(),
                ])
                ->action(function () {}),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [];
    }
}
