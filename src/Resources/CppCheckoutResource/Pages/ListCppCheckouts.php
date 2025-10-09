<?php

namespace Shieldforce\CheckoutPayment\Resources\CppCheckoutResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Shieldforce\CheckoutPayment\Resources\CppCheckoutResource;

class ListCppCheckouts extends ListRecords
{
    protected static string $resource = CppCheckoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
