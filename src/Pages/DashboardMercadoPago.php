<?php

namespace Shieldforce\CheckoutPayment\Pages;

use Filament\Pages\Page;
use Shieldforce\CheckoutPayment\Services\MercadoPago\MercadoPagoService;

class DashboardMercadoPago extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static string $view = 'checkout-payment::filament.pages.dashboard-mercado-pago';

    protected static ?string $navigationLabel = 'Dashboard MP';

    protected static ?string $title = 'Dashboard Mercado Pago';

    protected static ?string $navigationGroup = 'Checkout Payment';

    public array $payments = [];

    public array $stats = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData()
    {
        $mp = new MercadoPagoService;

        $this->payments = $mp->listarPagamentos(100);

        $this->stats = [
            'today' => collect($this->payments)
                ->where('status', 'approved')
                ->where('created', '>=', now()->startOfDay())
                ->sum('value'),

            'approved' => collect($this->payments)
                ->where('status', 'approved')
                ->sum('value'),

            'pending' => collect($this->payments)
                ->where('status', 'pending')
                ->count(),

            'rejected' => collect($this->payments)
                ->where('status', 'rejected')
                ->count(),

            'pix' => collect($this->payments)
                ->where('method', 'pix')
                ->sum('value'),

            'boleto' => collect($this->payments)
                ->filter(fn ($p) => str_contains($p['method'], 'bol'))
                ->sum('value'),
        ];
    }

    public function refreshData()
    {
        $this->loadData();
    }
}
