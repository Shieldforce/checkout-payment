<?php

namespace Shieldforce\CheckoutPayment\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Shieldforce\CheckoutPayment\Services\MercadoPago\MercadoPagoService;
use Shieldforce\CheckoutPayment\Services\Permissions\CanPageTrait;

class DashboardMercadoPago extends Page
{
    use CanPageTrait;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static string $view = 'checkout-payment::pages.dashboard-mercado-pago';

    protected static ?string $navigationLabel = 'Dashboard MP';

    protected static ?string $title = 'Dashboard Mercado Pago';

    protected static ?int $navigationSort = 2;

    public array $payments = [];

    public array $stats = [];

    public static function getSlug(): string
    {
        return 'dashboard-mercado-pago';
    }

    public function mount(): void
    {
        $this->loadData();
    }

    public function refreshData(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $mp = new MercadoPagoService();

        $this->payments = $mp->listarPagamentos(100);

        $payments = collect($this->payments);

        $approved = $payments->where('status', 'approved');

        $todayApproved = $payments->filter(function ($item) {
            if (empty($item['created'])) {
                return false;
            }

            return $item['status'] === 'approved'
                && now()->isSameDay(\Carbon\Carbon::parse($item['created']));
        });

        $pixToday = $payments->filter(function ($item) {
            if (empty($item['created'])) {
                return false;
            }

            return $item['method'] === 'pix'
                && $item['status'] === 'approved'
                && now()->isSameDay(\Carbon\Carbon::parse($item['created']));
        });

        $boletoPaid = $payments->filter(function ($item) {
            return str_contains(($item['method'] ?? ''), 'bol')
                && $item['status'] === 'approved';
        });

        $chargebacks = $payments->filter(function ($item) {
            return in_array($item['status'], [
                'charged_back',
                'refunded',
                'cancelled',
            ]);
        });

        $this->stats = [
            'today'       => $todayApproved->sum('value'),
            'approved'    => $approved->sum('value'),
            'pending'     => $payments->where('status', 'pending')->count(),
            'rejected'    => $payments->where('status', 'rejected')->count(),
            'pix_today'   => $pixToday->sum('value'),
            'boleto_paid' => $boletoPaid->sum('value'),
            'chargeback'  => $chargebacks->count(),
            'total'       => $payments->count(),
        ];
    }

    public function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('refresh')
                ->label('Atualizar')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->action(fn () => $this->refreshData()),
        ];
    }
}
