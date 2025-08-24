<?php

namespace Shieldforce\CheckoutPayment\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\SelectFilter;
use Shieldforce\CheckoutPayment\Models\Checkout;

class CPPGatewaysPage extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string  $view            = 'checkout-payment::pages.cpp_gateways';
    protected static ?string $navigationIcon  = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Gateways';
    protected static ?string $label           = 'Gateway';
    protected static ?string $navigationLabel = 'Gateway';
    protected static ?string $slug            = 'cpp-gateways';
    protected static ?string $title           = "Lista de Gateways";

    public function mount(?int $checkoutId = null): void {}

    public static function getNavigationGroup(): ?string
    {
        return config()->get('checkout-payment.sidebar_group');;
    }

    public $record;

    protected function getTableQuery()
    {
        return Checkout::query();
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
        return [
            SelectFilter::make('status')
                ->options([
                    'pending'   => 'Pending',
                    'paid'      => 'Paid',
                    'cancelled' => 'Cancelled',
                ]),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('name')->required(),
            TextInput::make('field_1')->required(),
            TextInput::make('field_2')->required(),
            TextInput::make('field_3')->required(),
            TextInput::make('field_4')->required(),
            TextInput::make('field_5')->required(),
            TextInput::make('field_6')->required(),
            Toggle::make('active')->required(),
        ];
    }

    public function save()
    {
        $data = $this->form->getState();

        if ($this->record) {
            $this->record->update($data);
        }
        else {
            $this->record = Checkout::create($data);
        }

        $this->notify('success', 'Gateway salvo com sucesso!');
        $this->resetForm();
    }

    public function edit($recordId)
    {
        $this->record = Checkout::findOrFail($recordId);
        $this->form->fill($this->record->toArray());
    }

    public function delete($recordId)
    {
        Checkout::findOrFail($recordId)->delete();
        $this->notify('success', 'Gateway deletado!');
    }
}

