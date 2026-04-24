<x-filament-panels::page>

    {{-- STATS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        {{--<div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow">
            <div class="text-sm text-gray-500">Recebido Hoje</div>
            <div class="text-2xl font-bold text-success-600">
                R$ {{ number_format($stats['today'] ?? 0, 2, ',', '.') }}
            </div>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow">
            <div class="text-sm text-gray-500">Total Aprovado</div>
            <div class="text-2xl font-bold text-primary-600">
                R$ {{ number_format($stats['approved'] ?? 0, 2, ',', '.') }}
            </div>
        </div>--}}
        <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow">
            <div class="text-sm text-gray-500">Pendentes</div>
            <div class="text-2xl font-bold text-warning-600">
                {{ $stats['pending'] ?? 0 }}
            </div>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow">
            <div class="text-sm text-gray-500">Rejeitados</div>
            <div class="text-2xl font-bold text-danger-600">
                {{ $stats['rejected'] ?? 0 }}
            </div>
        </div>
        {{--<div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow">
            <div class="text-sm text-gray-500">Pix Hoje</div>
            <div class="text-2xl font-bold text-success-600">
                R$ {{ number_format($stats['pix_today'] ?? 0, 2, ',', '.') }}
            </div>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow">
            <div class="text-sm text-gray-500">Boletos Pagos</div>
            <div class="text-2xl font-bold text-info-600">
                R$ {{ number_format($stats['boleto_paid'] ?? 0, 2, ',', '.') }}
            </div>
        </div>--}}
        <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow">
            <div class="text-sm text-gray-500">Chargeback / Estorno</div>
            <div class="text-2xl font-bold text-danger-600">
                {{ $stats['chargeback'] ?? 0 }}
            </div>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow">
            <div class="text-sm text-gray-500">Total Transações</div>
            <div class="text-2xl font-bold">
                {{ $stats['total'] ?? 0 }}
            </div>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="rounded-xl bg-white dark:bg-gray-900 shadow p-4 mb-4">
        <div class="font-semibold text-sm mb-3 text-gray-600 dark:text-gray-300">Filtros</div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">

            {{-- Transactons --}}
            {{--<div>
                <label class="block text-xs text-gray-500 mb-1">Entradas</label>
                <select
                    wire:model.live="transaction_id"
                    class="
                        w-full
                        rounded-lg
                        border
                        border-gray-300
                        dark:border-gray-700
                        dark:bg-gray-800
                        text-sm
                        px-3
                        py-2
                        focus:outline-none
                        focus:ring-2
                        focus:ring-primary-500
                    "
                >
                    <option value="">Todos</option>
                    @foreach($transactions as $transaction)
                        <option value="{{ $transaction->id ?? '' }}">
                            {{ $transaction->name }}
                        </option>
                    @endforeach
                </select>
            </div>--}}
            {{-- ✅ AUTOCOMPLETE de Transaction --}}
            <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
                <label class="block text-xs text-gray-500 mb-1">Entrada (Transaction)</label>

                <div class="relative">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="transaction_search"
                        x-on:focus="open = true"
                        x-on:input="open = true"
                        placeholder="Buscar por nome..."
                        autocomplete="off"
                        class="
                            w-full
                            rounded-lg
                            border
                            border-gray-300
                            dark:border-gray-700
                            dark:bg-gray-800
                            text-sm
                            px-3
                            py-2
                            pr-8
                            focus:outline-none
                            focus:ring-2
                            focus:ring-primary-500
                        "
                    />

                    {{-- Ícone de limpar (aparece quando tem seleção) --}}
                    @if($transaction_id)
                        <button
                            wire:click="clearTransaction"
                            type="button"
                            class="
                                absolute
                                right-2
                                top-1/2
                                -translate-y-1/2
                                text-gray-400
                                hover:text-danger-500
                                transition-colors
                            "
                            title="Limpar seleção"
                        >
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </button>
                    @else
                        <span
                            class="
                                absolute
                                right-2
                                top-1/2
                                -translate-y-1/2
                                text-gray-400
                                pointer-events-none
                            "
                        >
                            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                        </span>
                    @endif
                </div>

                {{-- Badge da transaction selecionada --}}
                @if($transaction_id)
                    <div class="mt-1">
                        <span
                            class="
                                inline-flex
                                items-center
                                gap-1
                                px-2
                                py-0.5
                                rounded-full
                                text-xs
                                font-medium
                                bg-primary-100
                                text-primary-700
                                dark:bg-primary-900
                                dark:text-primary-300
                            "
                        >
                            <x-heroicon-o-check class="w-3 h-3" />
                            Selecionada
                        </span>
                    </div>
                @endif

                {{-- Dropdown de resultados --}}
                <div
                    x-show="open && @js($transactions->isNotEmpty())"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="
                        absolute
                        z-50
                        mt-1
                        w-full
                        rounded-lg
                        border
                        border-gray-200
                        dark:border-gray-700
                        bg-white
                        dark:bg-gray-800
                        shadow-lg
                        max-h-56
                        overflow-y-auto
                    "
                >
                    {{-- Opção "Todos" --}}
                    <button
                        type="button"
                        wire:click="selectTransaction(null, '')"
                        x-on:click="open = false"
                        class="
                            w-full
                            text-left
                            px-3
                            py-2
                            text-sm
                            text-gray-500
                            hover:bg-gray-50
                            dark:hover:bg-gray-700
                            italic
                            border-b
                            dark:border-gray-700
                        "
                    >
                        Todos
                    </button>

                    @foreach($transactions as $transaction)
                        <button
                            type="button"
                            wire:click="selectTransaction({{ $transaction->id }}, '{{ addslashes($transaction->name) }}')"
                            x-on:click="open = false"
                            class="
                                w-full
                                text-left
                                px-3
                                py-2
                                text-sm
                                hover:bg-primary-50
                                dark:hover:bg-primary-900/40
                                transition-colors
                                {{ $transaction_id == $transaction->id
                                    ? 'bg-primary-50 dark:bg-primary-900/40 font-semibold text-primary-700 dark:text-primary-300'
                                    : 'text-gray-700 dark:text-gray-200'
                                }}
                            "
                        >
                            {{ $transaction->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select
                    wire:model.live="status"
                    class="
                        w-full
                        rounded-lg
                        border
                        border-gray-300
                        dark:border-gray-700
                        dark:bg-gray-800
                        text-sm
                        px-3
                        py-2
                        focus:outline-none
                        focus:ring-2
                        focus:ring-primary-500
                    "
                >
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

            {{-- Metodo --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1">Método de Pagamento</label>
                <select
                    wire:model.live="method"
                    class="
                        w-full
                        rounded-lg
                        border
                        border-gray-300
                        dark:border-gray-700
                        dark:bg-gray-800
                        text-sm
                        px-3
                        py-2
                        focus:outline-none
                        focus:ring-2
                        focus:ring-primary-500
                    "
                >
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
                <input
                    type="text"
                    wire:model.live.debounce.600ms="external"
                    placeholder="ex: ORDER-12345"
                    class="
                        w-full
                        rounded-lg
                        border
                        border-gray-300
                        dark:border-gray-700
                        dark:bg-gray-800
                        text-sm
                        px-3
                        py-2
                        focus:outline-none
                        focus:ring-2
                        focus:ring-primary-500
                    "
                />
            </div>

            {{-- Payer Email --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1">E-mail do Pagador</label>
                <input
                    type="text"
                    wire:model.live.debounce.600ms="payer"
                    placeholder="email@exemplo.com"
                    class="
                        w-full
                        rounded-lg
                        border
                        border-gray-300
                        dark:border-gray-700
                        dark:bg-gray-800
                        text-sm
                        px-3
                        py-2
                        focus:outline-none
                        focus:ring-2
                        focus:ring-primary-500
                    "
                />
            </div>

            {{-- Itens por página --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1">Por Página</label>
                <select
                    wire:model.live="limit"
                    class="
                        w-full
                        rounded-lg
                        border
                        border-gray-300
                        dark:border-gray-700
                        dark:bg-gray-800
                        text-sm
                        px-3
                        py-2
                        focus:outline-none
                        focus:ring-2
                        focus:ring-primary-500
                    "
                >
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

        </div>

        {{-- Ações dos filtros --}}
        <div class="mt-4 flex items-center justify-between">

            <button
                wire:click="resetFilters"
                class="
                    text-xs
                    text-gray-500
                    hover:text-danger-600
                    flex
                    items-center
                    gap-1
                    transition-colors
                "
            >
                <x-heroicon-o-x-circle class="w-4 h-4" />
                Limpar filtros
            </button>

            <button
                wire:click="applyFilters"
                class="
                    inline-flex
                    items-center
                    gap-2
                    px-4
                    py-2
                    rounded-lg
                    bg-primary-600
                    hover:bg-primary-700
                    text-white
                    text-sm
                    font-semibold
                    transition-colors
                "
            >
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
                    <th class="p-3 text-left">Vencimento</th>
                </tr>
                </thead>
                <tbody>
                @forelse($payments as $payment)
                    <tr class="border-t dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="p-3 font-mono text-xs">{{ $payment['id'] }}</td>
                        <td class="p-3">
                            <div>
                                {{ trim(($payment['document_type'] ?? '-')
                                    . ': ' . ($payment['document_number'] ?? '')) ?: '-'
                                }}
                            </div>
                            <div class="text-xs text-gray-400">{{ $payment['payer'] ?? '' }}</div>
                        </td>
                        <td class="p-3 uppercase text-xs font-semibold">{{ $payment['method'] ?? '-' }}</td>
                        <td class="p-3">
                            @php $status = $payment['status'] ?? '-'; @endphp
                            <span
                                class="
                                    px-2
                                    py-1
                                    rounded
                                    text-xs
                                    font-medium
                                    @if($status === 'approved')
                                        bg-green-100 text-green-700
                                    @elseif($status === 'pending' || $status === 'in_process' || $status === 'authorized')
                                        bg-yellow-100 text-yellow-700
                                    @elseif($status === 'rejected')
                                        bg-red-100 text-red-700
                                    @elseif(in_array($status, ['cancelled','refunded','charged_back']))
                                        bg-orange-100 text-orange-700
                                    @else
                                        bg-gray-100 text-gray-700
                                    @endif
                                "
                            >
                                {{ $status }}
                            </span>
                        </td>
                        <td class="p-3 font-semibold">
                            R$ {{ number_format($payment['value'] ?? 0, 2, ',', '.') }}
                        </td>
                        {{-- ✅ COLUNA REF. EXTERNA com botões Filament --}}
                        <td class="p-3 font-semibold">
                            <div class="flex flex-col gap-1">

                                {{-- UUID da referência (exibe apenas se não tiver transaction/order vinculado) --}}
                                @if(!$payment['transaction_id'] && !$payment['order_id'])
                                    <span class="font-mono text-xs text-gray-500">
                                        {{ $payment['external'] ?? '-' }}
                                    </span>
                                @endif

                                {{-- Botão: editar Transaction --}}
                                @if($payment['transaction_id'])
                                    <a
                                        href="{{ \App\Filament\Resources\TransactionResource::getUrl(
                                            'edit',
                                            ['record' => $payment['transaction_id']]
                                        ) }}"
                                        target="_blank"
                                        class="
                                            inline-flex
                                            items-center
                                            gap-1
                                            px-2
                                            py-1
                                            rounded-md
                                            text-xs
                                            font-medium
                                            bg-primary-50
                                            text-primary-700
                                            hover:bg-primary-100
                                            dark:bg-primary-950
                                            dark:text-primary-300
                                            dark:hover:bg-primary-900
                                            transition-colors
                                            w-fit
                                        "
                                    >
                                        <x-heroicon-o-arrows-right-left class="w-3 h-3" />
                                        Entrada #{{ $payment['transaction_id'] }}
                                    </a>
                                @endif

                                {{-- Botão: editar Order --}}
                                @if($payment['order_id'])
                                    <a
                                        href="{{ \App\Filament\Resources\OrderResource::getUrl(
                                            'edit',
                                            ['record' => $payment['order_id']]
                                        ) }}"
                                        target="_blank"
                                        class="
                                            inline-flex
                                            items-center
                                            gap-1
                                            px-2
                                            py-1
                                            rounded-md
                                            text-xs
                                            font-medium
                                            bg-success-50
                                            text-success-700
                                            hover:bg-success-100
                                            dark:bg-success-950
                                            dark:text-success-300
                                            dark:hover:bg-success-900
                                            transition-colors w-fit
                                        "
                                    >
                                        <x-heroicon-o-shopping-cart class="w-3 h-3" />
                                        Pedido #{{ $payment['order_id'] }}
                                    </a>
                                @endif

                            </div>
                        </td>
                        <td class="p-3 text-xs">
                            {{ !empty($payment['created'])
                            ? \Carbon\Carbon::parse($payment['created'])->format('d/m/Y H:i')
                            : '-' }}
                        </td>
                        <td class="p-3 text-xs">
                            {{ !empty($payment['due_date'])
                            ? \Carbon\Carbon::parse($payment['due_date'])->format('d/m/Y H:i')
                            : '-' }}
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
            $limit   = $paging['limit'] ?? $limit ?? 50;
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
                    <button
                        wire:click="goToPage(1)" @if($page <= 1) disabled @endif
                    class="
                            px-2
                            py-1
                            rounded
                            text-sm
                            {{ $page <= 1
                                ? 'text-gray-300 cursor-not-allowed'
                                : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300'
                            }}
                        "
                    >
                        «
                    </button>

                    {{-- Anterior --}}
                    <button
                        wire:click="prevPage" @if($page <= 1) disabled @endif
                    class="
                            px-3
                            py-1
                            rounded
                            text-sm
                            {{ $page <= 1
                                ? 'text-gray-300 cursor-not-allowed'
                                : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300'
                            }}
                        "
                    >
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
                    <button
                        wire:click="nextPage" @if($page >= $pages) disabled @endif
                        class="
                            px-3
                            py-1
                            rounded
                            text-sm
                            {{ $page >= $pages
                                ? 'text-gray-300 cursor-not-allowed'
                                : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300'
                            }}
                        "
                    >
                        Próxima ›
                    </button>

                    {{-- Última --}}
                    <button
                        wire:click="goToPage({{ $pages }})" @if($page >= $pages) disabled @endif
                        class="
                            px-2
                            py-1
                            rounded
                            text-sm
                            {{ $page >= $pages
                                ? 'text-gray-300 cursor-not-allowed'
                                : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300'
                            }}
                        "
                    >
                        »
                    </button>

                </div>
            </div>
        @endif

    </div>

</x-filament-panels::page>
