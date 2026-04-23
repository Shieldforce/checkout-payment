<?php

/*
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

        $this->payments = $mp->listarPagamentos(50);

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
}*/

namespace Shieldforce\CheckoutPayment\Pages;

use Carbon\Carbon;
use Filament\Actions\Action;
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

    public array $paging = [];

    public int $page = 1;

    public int $limit = 50;

    public string $status = '';

    public string $external = '';

    public string $payer = '';

    public string $method = '';

    public static function getSlug(): string
    {
        return 'dashboard-mercado-pago';
    }

    public function mount(): void
    {
        $this->loadData();
    }

    public function updated($field): void
    {
        if (in_array($field, [
            'status',
            'external',
            'payer',
            'method',
        ])) {
            $this->page = 1;
            $this->loadData();
        }
    }

    public function nextPage(): void
    {
        $this->page++;
        $this->loadData();
    }

    public function prevPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
            $this->loadData();
        }
    }

    public function refreshData(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $offset = ($this->page - 1) * $this->limit;

        $filters = [
            'status' => $this->status,
            'external_reference' => $this->external,
            'payer.email' => $this->payer,
            'payment_method_id' => $this->method,
        ];

        $mp = new MercadoPagoService;

        $result = $mp->listarPagamentos(
            $this->limit,
            $offset,
            $filters
        );

        $this->payments = $result['data'] ?? [];
        $this->paging = $result['paging'] ?? [];

        $payments = collect($this->payments);

        $approved = $payments->where('status', 'approved');

        $todayApproved = $payments->filter(function ($item) {
            if (empty($item['created'])) {
                return false;
            }

            return $item['status'] === 'approved'
                && now()->isSameDay(Carbon::parse($item['created']));
        });

        $pixToday = $payments->filter(function ($item) {
            if (empty($item['created'])) {
                return false;
            }

            return $item['method'] === 'pix'
                && $item['status'] === 'approved'
                && now()->isSameDay(Carbon::parse($item['created']));
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
            'today' => $todayApproved->sum('value'),
            'approved' => $approved->sum('value'),
            'pending' => $payments->where('status', 'pending')->count(),
            'rejected' => $payments->where('status', 'rejected')->count(),
            'pix_today' => $pixToday->sum('value'),
            'boleto_paid' => $boletoPaid->sum('value'),
            'chargeback' => $chargebacks->count(),
            'total' => $this->paging['total'] ?? 0,
        ];
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Atualizar')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->action(fn () => $this->refreshData()),
        ];
    }
}
