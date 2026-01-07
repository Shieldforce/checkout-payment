<?php

namespace Shieldforce\CheckoutPayment\Resources\CppCheckoutResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Carbon;
use Shieldforce\CheckoutPayment\Resources\CppCheckoutResource;
use App\Jobs\GenerateMonthlyBillingsJob;
use Filament\Notifications\Notification;

class ListCppCheckouts extends ListRecords
{
    protected static string $resource = CppCheckoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('runMonthlyBilling')
                ->label('Rodar Faturamento Mensal')
                ->icon('heroicon-o-play')
                ->color('success')

                // 👉 FORM DO MODAL
                ->form([
                    TextInput::make('reference')
                        ->label('Referência')
                        ->helperText('Ex: 01/2026')
                        ->default(now()->subMonth()->format('m/Y'))
                        ->required(),

                    /*TextInput::make('billingDay')
                        ->label('Dia de Cobrança')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(31)
                        ->default(now()->format("d"))
                        ->required(),*/

                    Select::make('billingDay')
                        ->label('Dia de Cobrança')
                        ->required()
                        ->options(function (callable $get) {
                            $reference = $get('reference');

                            try {
                                [$month, $year] = explode('/', $reference);
                                $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
                            } catch (\Throwable) {
                                $daysInMonth = now()->daysInMonth;
                            }

                            return collect(range(1, $daysInMonth))
                                ->mapWithKeys(fn ($day) => [
                                    str_pad($day, 2, '0', STR_PAD_LEFT) => str_pad($day, 2, '0', STR_PAD_LEFT)
                                ])
                                ->toArray();
                        })
                        ->default(fn () => str_pad(now()->day, 2, '0', STR_PAD_LEFT)),
                ])

                ->modalHeading('Gerar cobranças mensais')
                ->modalDescription(
                    'Informe a referência e o dia de cobrança para gerar as cobranças manualmente.'
                )
                ->modalSubmitActionLabel('Executar faturamento')

                // 👉 AÇÃO FINAL
                ->action(function (array $data) {

                    GenerateMonthlyBillingsJob::dispatch(
                        $data['reference'],
                        (int) $data['billingDay'],
                    );

                    Notification::make()
                        ->title('Faturamento iniciado')
                        ->body("Referência {$data['reference']} • Dia {$data['billingDay']}")
                        ->success()
                        ->send();
                }),
        ];
    }
}
