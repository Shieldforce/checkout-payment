<?php

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

    protected static ?string $navigationIcon  = 'heroicon-o-credit-card';
    protected static string  $view            = 'checkout-payment::pages.dashboard-mercado-pago';
    protected static ?string $navigationLabel = 'Dashboard MP';
    protected static ?string $title           = 'Dashboard Mercado Pago';
    protected static ?string $navigationGroup = "Financeiro";
    protected static ?int    $navigationSort  = 2;

    /* VARIÁVEIS */
    public array   $payments             = [];
    public array   $stats                = [];
    public array   $paging               = [];
    public int     $page                 = 1;
    public int     $limit                = 50;
    public string  $status               = '';
    public string  $external             = '';
    public string  $payer                = '';
    public string  $method               = '';
    public string  $date_from            = '';
    public string  $date_to              = '';
    public string  $date_approved_from   = '';
    public string  $date_approved_to     = '';
    public string  $date_expiration_from = '';
    public string  $date_expiration_to   = '';
    public ?string $sort                 = 'date_created';

    public static function getSlug(): string
    {
        return 'dashboard-mercado-pago';
    }

    public function mount(): void
    {
        $this->loadData();
    }

    public function nextPage(): void
    {
        if ($this->page < $this->getTotalPages()) {
            $this->page++;
            $this->loadData();
        }
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

    public function resetFilters(): void
    {
        $this->status   = '';
        $this->external = '';
        $this->payer    = '';
        $this->method   = '';
        $this->page     = 1;
        $this->sort     = 'date_created';
        $this->loadData();
    }

    public function getTotalPages(): int
    {
        $total = $this->paging['total'] ?? 0;

        return $total > 0 ? (int)ceil($total / $this->limit) : 1;
    }

    public function loadData(): void
    {
        $offset = ($this->page - 1) * $this->limit;

        $filters = array_filter([
            'status'                  => $this->status ?: null,
            'external_reference'      => $this->external ?: null,
            'payer.email'             => $this->payer ?: null,
            'payment_method_id'       => $this->method ?: null,
            'sort'                    => $this->sort ?: null,
            'begin_date'              => $this->date_from
                ? Carbon::parse($this->date_from)->startOfDay()->toIso8601String()
                : null,
            'end_date'                => $this->date_to
                ? Carbon::parse($this->date_to)->endOfDay()->toIso8601String()
                : null,
            'date_approved.from'      => $this->date_approved_from
                ? Carbon::parse($this->date_approved_from)->startOfDay()->toIso8601String()
                : null,
            'date_approved.to'        => $this->date_approved_to
                ? Carbon::parse($this->date_approved_to)->endOfDay()->toIso8601String()
                : null,
            'date_of_expiration.from' => $this->date_expiration_from
                ? Carbon::parse($this->date_expiration_from)->startOfDay()->toIso8601String()
                : null,
            'date_of_expiration.to'   => $this->date_expiration_to
                ? Carbon::parse($this->date_expiration_to)->endOfDay()->toIso8601String()
                : null,
        ], fn($v) => $v !== null);

        $mp = new MercadoPagoService();

        $result = $mp->listarPagamentos(
            $this->limit,
            $offset,
            $filters
        );

        $this->payments = $result['data'] ?? [];
        $this->paging   = $result['paging'] ?? [];

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
            'today'       => $todayApproved->sum('value'),
            'approved'    => $approved->sum('value'),
            'pending'     => $payments->where('status', 'pending')->count(),
            'rejected'    => $payments->where('status', 'rejected')->count(),
            'pix_today'   => $pixToday->sum('value'),
            'boleto_paid' => $boletoPaid->sum('value'),
            'chargeback'  => $chargebacks->count(),
            'total'       => $this->paging['total'] ?? 0,
        ];
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Atualizar')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->action(fn() => $this->refreshData()),
        ];
    }

    public function goToPage(int $page): void
    {
        $this->page = $page;
        $this->loadData();
    }

    public function applyFilters(): void
    {
        $this->page = 1;
        $this->loadData();
    }
}
