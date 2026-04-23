<x-filament-panels::page>

    {{-- STATS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow">
            <div class="text-sm text-gray-500">Recebido Hoje</div>
            <div class="text-2xl font-bold text-success-600">R$ {{ number_format($stats['today'] ?? 0, 2, ',', '.') }}</div>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow">
            <div class="text-sm text-gray-500">Total Aprovado</div>
            <div class="text-2xl font-bold text-primary-600">R$ {{ number_format($stats['approved'] ?? 0, 2, ',', '.') }}</div>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow">
            <div class="text-sm text-gray-500">Pendentes</div>
            <div class="text-2xl font-bold text-warning-600">{{ $stats['pending'] ?? 0 }}</div>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow">
            <div class="text-sm text-gray-500">Rejeitados</div>
            <div class="text-2xl font-bold text-danger-600">{{ $stats['rejected'] ?? 0 }}</div>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow">
            <div class="text-sm text-gray-500">Pix Hoje</div>
            <div class="text-2xl font-bold text-success-600">R$ {{ number_format($stats['pix_today'] ?? 0, 2, ',', '.') }}</div>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow">
            <div class="text-sm text-gray-500">Boletos Pagos</div>
            <div class="text-2xl font-bold text-info-600">R$ {{ number_format($stats['boleto_paid'] ?? 0, 2, ',', '.') }}</div>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow">
            <div class="text-sm text-gray-500">Chargeback / Estorno</div>
            <div class="text-2xl font-bold text-danger-600">{{ $stats['chargeback'] ?? 0 }}</div>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow">
            <div class="text-sm text-gray-500">Total Transações</div>
            <div class="text-2xl font-bold">{{ $stats['total'] ?? 0 }}</div>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="rounded-xl bg-white dark:bg-gray-900 shadow p-4 mb-4">
        <div class="font-semibold text-sm mb-3 text-gray-600 dark:text-gray-300">Filtros</div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">

            {{-- Status --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select wire:model.live="status"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Todos</option>
                    <option value="approved">Aprovado</option>
                    <option value="pending">Pendente</option>
                    <option value="rejected">Rejeitado</option>
                    <option value="cancelled">Cancelado</option>
                    <option value="refunded">Estornado</option>
                    <option value="charged_back">Chargeback</option>
                    <option value="in_process">Em processo</option>
                    <option value="authorized">Autorizado</option>
                </select>
            </div>

            {{-- Método --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1">Método de Pagamento</label>
                <select wire:model.live="method"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Todos</option>
                    <option value="pix">Pix</option>
                    <option value="bolbradesco">Boleto Bradesco</option>
                    <option value="boletobancario">Boleto Bancário</option>
                    <option value="visa">Visa</option>
                    <option value="master">Mastercard</option>
                    <option value="amex">Amex</option>
                    <option value="elo">Elo</option>
                    <option value="hipercard">Hipercard</option>
                    <option value="account_money">Saldo MP</option>
                </select>
            </div>

            {{-- External Reference --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1">Referência Externa</label>
                <input type="text"
                       wire:model.live.debounce.600ms="external"
                       placeholder="ex: ORDER-12345"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500" />
            </div>

            {{-- Payer Email --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1">E-mail do Pagador</label>
                <input type="text"
                       wire:model.live.debounce.600ms="payer"
                       placeholder="email@exemplo.com"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500" />
            </div>

            {{-- Itens por página --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1">Por Página</label>
                <select wire:model.live="limit"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

        </div>

        {{-- Ações dos filtros --}}
        <div class="mt-4 flex items-center justify-between">

            <button wire:click="resetFilters"
                    class="text-xs text-gray-500 hover:text-danger-600 flex items-center gap-1 transition-colors">
                <x-heroicon-o-x-circle class="w-4 h-4" />
                Limpar filtros
            </button>

            <button wire:click="applyFilters"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold transition-colors">
                <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                Filtrar
            </button>

        </div>
    </div>

    {{-- TABELA --}}
    <div class="rounded-xl bg-white dark:bg-gray-900 shadow overflow-hidden">

        <div class="p-4 border-b dark:border-gray-800 flex items-center justify-between">
            <span class="font-bold text-lg">Transações Mercado Pago</span>
            <span class="text-sm text-gray-500">
                {{ number_format($paging['total'] ?? 0, 0, ',', '.') }} registros no total
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="p-3 text-left">ID</th>
                    <th class="p-3 text-left">Cliente</th>
                    <th class="p-3 text-left">Método</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">Valor</th>
                    <th class="p-3 text-left">Ref. Externa</th>
                    <th class="p-3 text-left">Criado em</th>
                </tr>
                </thead>
                <tbody>
                @forelse($payments as $payment)
                    <tr class="border-t dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="p-3 font-mono text-xs">{{ $payment['id'] }}</td>
                        <td class="p-3">
                            <div>{{ trim(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? '')) ?: '-' }}</div>
                            <div class="text-xs text-gray-400">{{ $payment['payer'] ?? '' }}</div>
                        </td>
                        <td class="p-3 uppercase text-xs font-semibold">{{ $payment['method'] ?? '-' }}</td>
                        <td class="p-3">
                            @php $status = $payment['status'] ?? '-'; @endphp
                            <span class="px-2 py-1 rounded text-xs font-medium
                                @if($status === 'approved') bg-green-100 text-green-700
                                @elseif($status === 'pending' || $status === 'in_process' || $status === 'authorized') bg-yellow-100 text-yellow-700
                                @elseif($status === 'rejected') bg-red-100 text-red-700
                                @elseif(in_array($status, ['cancelled','refunded','charged_back'])) bg-orange-100 text-orange-700
                                @else bg-gray-100 text-gray-700
                                @endif">
                                {{ $status }}
                            </span>
                        </td>
                        <td class="p-3 font-semibold">R$ {{ number_format($payment['value'] ?? 0, 2, ',', '.') }}</td>
                        <td class="p-3 text-xs font-mono">{{ $payment['external'] ?? '-' }}</td>
                        <td class="p-3 text-xs">
                            {{ !empty($payment['created']) ? \Carbon\Carbon::parse($payment['created'])->format('d/m/Y H:i') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-400">
                            Nenhuma transação encontrada.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINAÇÃO --}}
        @php
            $total   = $paging['total'] ?? 0;
            $limit   = $paging['limit'] ?? $limit;
            $offset  = $paging['offset'] ?? 0;
            $pages   = $limit > 0 ? (int) ceil($total / $limit) : 1;
            $from    = $total > 0 ? $offset + 1 : 0;
            $to      = min($offset + $limit, $total);
        @endphp

        @if($total > 0)
            <div class="p-4 border-t dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-3">

                <div class="text-sm text-gray-500">
                    Exibindo <strong>{{ $from }}</strong>–<strong>{{ $to }}</strong>
                    de <strong>{{ number_format($total, 0, ',', '.') }}</strong> registros
                    &nbsp;|&nbsp; Página <strong>{{ $page }}</strong> de <strong>{{ $pages }}</strong>
                </div>

                <div class="flex items-center gap-1">

                    {{-- Primeira --}}
                    <button wire:click="goToPage(1)" @if($page <= 1) disabled @endif
                    class="px-2 py-1 rounded text-sm {{ $page <= 1 ? 'text-gray-300 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                        «
                    </button>

                    {{-- Anterior --}}
                    <button wire:click="prevPage" @if($page <= 1) disabled @endif
                    class="px-3 py-1 rounded text-sm {{ $page <= 1 ? 'text-gray-300 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                        ‹ Anterior
                    </button>

                    {{-- Números de página (janela de 5) --}}
                    @php
                        $window = 2;
                        $start  = max(1, $page - $window);
                        $end    = min($pages, $page + $window);
                    @endphp

                    @for($p = $start; $p <= $end; $p++)
                        <button wire:click="goToPage({{ $p }})"
                                class="px-3 py-1 rounded text-sm
                                {{ $p == $page
                                    ? 'bg-primary-600 text-white font-bold'
                                    : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                                            {{ $p }}
                        </button>
                    @endfor

                    {{-- Próxima --}}
                    <button wire:click="nextPage" @if($page >= $pages) disabled @endif
                    class="px-3 py-1 rounded text-sm {{ $page >= $pages ? 'text-gray-300 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                        Próxima ›
                    </button>

                    {{-- Última --}}
                    <button wire:click="goToPage({{ $pages }})" @if($page >= $pages) disabled @endif
                    class="px-2 py-1 rounded text-sm {{ $page >= $pages ? 'text-gray-300 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                        »
                    </button>

                </div>
            </div>
        @endif

    </div>

</x-filament-panels::page>
