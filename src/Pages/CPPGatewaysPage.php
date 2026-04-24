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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Shieldforce\CheckoutPayment\Enums\TypeGatewayEnum;
use Shieldforce\CheckoutPayment\Models\CppGateways;
use Shieldforce\CheckoutPayment\Services\ManagerFieldService;
use Shieldforce\CheckoutPayment\Services\Permissions\CanPageTrait;

class CPPGatewaysPage extends Page implements HasForms, HasTable
{
    use CanPageTrait;
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
            ->filters(
                $this->getTableFilters(),
                layout: FiltersLayout::AboveContentCollapsible
            )
            ->filtersFormColumns(3)
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Filtrar...')
            )
            ->bulkActions($this->getTableBulkActions())
            ->actions($this->getTableActions());
    }

    public static function getNavigationGroup(): ?string
    {
        return config()->get('checkout-payment.sidebar_group');
    }

    protected function getTableQuery(): Builder
    {
        return CppGateways::query();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')
                ->sortable(),

            TextColumn::make('name')
                ->label('Gateway')
                ->formatStateUsing(
                    fn($state) => TypeGatewayEnum::from($state)->label()
                ),

            ToggleColumn::make('active')
                ->label('Ativo'),

            TextColumn::make('created_at')
                ->label('Criado em')
                ->since(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('active')
                ->label('Status')
                ->options([
                    1 => 'Ativo',
                    0 => 'Inativo',
                ]),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->modelLabel('Editar gateway')
                ->modalSubmitActionLabel('Salvar')
                ->mutateRecordDataUsing(function (array $data): array {
                    $data['field_1'] = $this->safeDecrypt($data['field_1'] ?? null);
                    $data['field_2'] = $this->safeDecrypt($data['field_2'] ?? null);

                    return $data;
                })
                ->form($this->fields())
                ->action(function (array $data, $record) {
                    $data = $this->encryptFieldsIfNeeded($data);

                    $record->update($data);
                }),

            DeleteAction::make(),
        ];
    }

    public function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('create')
                ->label('Adicionar')
                ->modalSubmitActionLabel('Salvar')
                ->form($this->fields())
                ->action(function (array $data) {
                    $data = $this->encryptFieldsIfNeeded($data);

                    if (!empty($data['active'])) {
                        CppGateways::where('active', true)->update([
                            'active' => false,
                        ]);
                    }

                    CppGateways::updateOrCreate(
                        ['name' => $data['name']],
                        $data
                    );
                }),
        ];
    }

    public function fields(): array
    {
        return [
            Select::make('name')
                ->label('Gateway')
                ->live()
                ->options(
                    collect(TypeGatewayEnum::cases())
                        ->mapWithKeys(
                            fn($case) => [$case->value => $case->label()]
                        )
                        ->toArray()
                )
                ->columnSpanFull()
                ->required(),

            Grid::make()
                ->schema([
                    ManagerFieldService::TextInput('field_1'),
                    ManagerFieldService::TextInput('field_2'),
                ])
                ->columns(2),

            Grid::make()
                ->schema([
                    ManagerFieldService::TextInput('field_3'),
                    ManagerFieldService::TextInput('field_4'),
                    ManagerFieldService::TextInput('field_5'),
                ])
                ->columns(3),

            Grid::make()
                ->schema([
                    ManagerFieldService::TextInput('field_6'),
                ])
                ->columns(3),

            Toggle::make('active')
                ->label('Ativo')
                ->default(true)
                ->hint('Ao ativar esse gateway, os outros serão desativados.')
                ->required(),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check();
    }

    private function encryptFieldsIfNeeded(array $data): array
    {
        if (($data['name'] ?? null) === TypeGatewayEnum::mercado_pago->value) {
            $data['field_1'] = $this->safeEncrypt($data['field_1'] ?? null);
            $data['field_2'] = $this->safeEncrypt($data['field_2'] ?? null);
        }

        if (!empty($data['active'])) {
            CppGateways::where('active', true)->update([
                'active' => false,
            ]);
        }

        return $data;
    }

    private function safeEncrypt(?string $value): ?string
    {
        if (blank($value)) {
            return $value;
        }

        try {
            Crypt::decryptString($value);

            return $value;
        } catch (\Throwable $e) {
            return Crypt::encryptString($value);
        }
    }

    private function safeDecrypt(?string $value): ?string
    {
        if (blank($value)) {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return $value;
        }
    }
}
