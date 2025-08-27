<?php

namespace Shieldforce\CheckoutPayment\Resources\CppCheckoutResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Shieldforce\CheckoutPayment\Resources\CppCheckoutResource;

class EditCppCheckout extends EditRecord
{
    protected static string $resource = CppCheckoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
