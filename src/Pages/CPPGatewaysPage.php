<?php

namespace Shieldforce\CheckoutPayment\Pages;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
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

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return CppGateways::query();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')->sortable(),
            TextColumn::make('name')
                ->label('Gateway'),

            TextColumn::make('field_1')
                ->label(fn (Model $record) => TypeGatewayEnum::from($record->name)->labelFields()['field_1']),
            TextColumn::make('field_2')
                ->label('Campo 2'),
            TextColumn::make('field_3')
                ->label('Campo 3'),
            TextColumn::make('field_4')
                ->label('Campo 4'),
            TextColumn::make('field_5')
                ->label('Campo 5'),
            TextColumn::make('field_6')
                ->label('Campo 6'),

            ToggleColumn::make('active')
                ->label('Ativo'),

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
                    Select::make('name')
                        ->label('Gateway')
                        ->live()
                        ->options(function () {
                            return collect(TypeGatewayEnum::cases())
                                ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                                ->toArray();
                        })
                        ->columnSpanFull()
                        ->required(),

                    Grid::make()->schema([

                        ManagerFieldService::TextInput('field_1')
                            ->helperText('Os campos sensíveis são cryptografados!'),
                        ManagerFieldService::TextInput('field_2')
                            ->helperText('Os campos sensíveis são cryptografados!'),

                    ])->columns(2),
                    Grid::make()->schema([

                        ManagerFieldService::TextInput('field_3'),
                        ManagerFieldService::TextInput('field_4'),
                        ManagerFieldService::TextInput('field_5'),

                    ])->columns(3),
                    Grid::make()->schema([

                        ManagerFieldService::TextInput('field_6'),

                    ])->columns(3),

                    Toggle::make('active')
                        ->label('Ativo')
                        ->default(true)
                        ->hint('Ao ativar esse gateway, os outro serão desativados.')
                        ->required(),

                ])
                ->action(function (array $data) {
                    $active = $data['active'];
                    unset($data['active']);

                    if (TypeGatewayEnum::from($data['name']) == TypeGatewayEnum::mercado_pago) {
                        $data['field_1'] = Crypt::encrypt($data['field_1']);
                        $data['field_2'] = Crypt::encrypt($data['field_2']);
                    }

                    CppGateways::updateOrCreate($data, ['active' => $active]);
                }),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [];
    }
}
