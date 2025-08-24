<?php

namespace Shieldforce\CheckoutPayment\Pages;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
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
use Shieldforce\CheckoutPayment\Enums\TypeGatewayEnum;
use Shieldforce\CheckoutPayment\Models\CppGateways;
use Shieldforce\CheckoutPayment\Services\ManagerFieldService;

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
                fn (Action $action) => $action
                    ->button()
                    ->label('Filtrar...'),
            )
            ->bulkActions($this->getTableBulkActions())
            ->actions($this->getTableActions());
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
                    'pending' => 'Pending',
                    'paid' => 'Paid',
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

    public function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('create')
                ->label('Adicionar')
                ->form([
                    Grid::make()->schema([

                        Select::make('name')
                            ->label('Gateway')
                            ->live()
                            ->options(function () {
                                return collect(TypeGatewayEnum::cases())
                                    ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                                    ->toArray();
                            })
                            ->required(),
                        ManagerFieldService::TextInput('field_1'),
                        TextInput::make('field_2')
                            ->reactive()
                            ->required(),

                    ])->columns(3),
                    Grid::make()->schema([

                        TextInput::make('field_3')
                            ->reactive()
                            ->required(),
                        TextInput::make('field_4')
                            ->reactive()
                            ->required(),
                        TextInput::make('field_5')
                            ->reactive()
                            ->required(),

                    ])->columns(3),
                    Grid::make()->schema([

                        TextInput::make('field_6')
                            ->reactive()
                            ->required(),

                    ])->columns(3),

                    Toggle::make('active')
                        ->required(),

                ])
                ->action(function (array $data) {
                    $active = $data['active'];
                    unset($data['active']);
                    CppGateways::updateOrCreate($data, ['active' => $active]);
                }),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [];
    }
}
