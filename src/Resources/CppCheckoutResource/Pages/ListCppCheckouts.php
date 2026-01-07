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
        $year = now()->year;

        return [
            Actions\Action::make('runMonthlyBilling')
                ->label('Rodar Faturamento Mensal')
                ->icon('heroicon-o-play')
                ->color('success')

                // 👉 FORM DO MODAL
                ->form([
                    /*TextInput::make('reference')
                        ->label('Referência')
                        ->helperText('Ex: 01/2026')
                        ->default(now()->subMonth()->format('m/Y'))
                        ->required(),*/

                    Select::make('reference')
                        ->label('Referência')
                        ->required()
                        ->options([
                            "01/{$year}" => "01/{$year} - Janeiro",
                            "02/{$year}" => "02/{$year} - Fevereiro",
                            "03/{$year}" => "03/{$year} - Março",
                            "04/{$year}" => "04/{$year} - Abril",
                            "05/{$year}" => "05/{$year} - Maio",
                            "06/{$year}" => "06/{$year} - Junho",
                            "07/{$year}" => "07/{$year} - Julho",
                            "08/{$year}" => "08/{$year} - Agosto",
                            "09/{$year}" => "09/{$year} - Setembro",
                            "10/{$year}" => "10/{$year} - Outubro",
                            "11/{$year}" => "11/{$year} - Novembro",
                            "12/{$year}" => "12/{$year} - Dezembro",
                        ])
                        ->default(now()->subMonth()->format('m'))
                        ->live(),

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
