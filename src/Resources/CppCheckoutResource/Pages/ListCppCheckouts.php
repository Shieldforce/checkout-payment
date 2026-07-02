<?php

namespace Shieldforce\CheckoutPayment\Resources\CppCheckoutResource\Pages;

use App\Jobs\GenerateMonthlyBillingsJob;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Carbon;
use Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum;
use Shieldforce\CheckoutPayment\Models\CppCheckout;
use Shieldforce\CheckoutPayment\Pages\DashboardMercadoPago;
use Shieldforce\CheckoutPayment\Resources\CppCheckoutResource;
use Shieldforce\CheckoutPayment\Services\MercadoPago\MercadoPagoService;

class ListCppCheckouts extends ListRecords
{
    protected static string $resource = CppCheckoutResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\Action::make('dashboard_mp')
                ->label('Dashboard MP')
                ->icon('heroicon-o-chart-bar')
                ->color('success')
                ->url(fn () => DashboardMercadoPago::getUrl())
            /* ->openUrlInNewTab() */,

            Actions\Action::make('runMonthlyBilling')
                ->label('Rodar Faturamento Mensal')
                ->icon('heroicon-o-play')
                ->color('success')

                // 👉 FORM DO MODAL
                ->form([
                    TextInput::make('reference')
                        ->label('Referência - Exp: 12/2025')
                        ->helperText('Ex: 01/2026')
                        ->default(now()->subMonth()->format('m/Y'))
                        ->required(),

                    Select::make('billingDay')
                        ->label('Dia de Cobrança')
                        ->required()
                        ->options(function (callable $get) {
                            $reference = $get('reference');

                            try {
                                [
                                    $month,
                                    $year
                                ] = explode('/', $reference);
                                $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
                            } catch (\Throwable) {
                                $daysInMonth = now()->daysInMonth;
                            }

                            $days = collect(range(1, $daysInMonth))
                                ->mapWithKeys(fn ($day) => [
                                    str_pad($day, 2, '0', STR_PAD_LEFT) => str_pad($day, 2, '0', STR_PAD_LEFT),
                                ])
                                ->toArray();

                            // adiciona "Todos os dias" no topo
                            return ['all' => 'Todos os dias'] + $days;
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
                        $data['billingDay'],
                    );

                    Notification::make()
                        ->title('Faturamento iniciado')
                        ->body("Referência {$data['reference']} • Dia {$data['billingDay']}")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function atualizarPagamento($paymentId, $method, $recordId)
    {
        dd($paymentId, $method, $recordId);
    }

    public function cancelarPagamentoMp($paymentId, $recordId): void
    {
        $mps = new MercadoPagoService;

        $cancel = $mps->cancelarPagamento($paymentId);

        logger([
            'payment_id' => $paymentId,
            'cancel' => $cancel,
        ]);

        if ($cancel['success']) {

            $checkout = CppCheckout::find($recordId);

            $checkout->update([
                'status' => StatusCheckoutEnum::cancelado->value,
                'startOnStep' => 5,
            ]);

            $step4 = $checkout->step4->first();

            $step4->update([
                'card_number' => null,
                'card_token' => null,
                'installments' => null,
                'payment_method_id' => null,
                'card_validate' => null,
                'card_payer_name' => null,
                'base_qrcode' => null,
                'url_qrcode' => null,
                'url_billet' => null,
                'request_credit_card_data' => null,
                'response_credit_card_data' => null,
                'request_pix_data' => null,
                'response_pix_data' => null,
                'request_billet_data' => json_encode([]),
                'response_billet_data' => json_encode([]),
            ]);

            Notification::make()
                ->success()
                ->title('Pagamento cancelado!')
                ->body("Pagamento #{$paymentId} cancelado com sucesso.")
                ->send();

        } else {

            Notification::make()
                ->danger()
                ->title('Erro ao cancelar!')
                ->body($cancel['message'] ?? 'Erro desconhecido.')
                ->send();
        }
    }
}
