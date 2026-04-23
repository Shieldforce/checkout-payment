<x-filament-panels::page>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

        <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow">
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
        </div>

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

        <div class="rounded-xl bg-white dark:bg-gray-900 p-4 shadow">
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
        </div>

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


    <div class="rounded-xl bg-white dark:bg-gray-900 shadow overflow-hidden">

        <div class="p-4 border-b dark:border-gray-800 font-bold text-lg">
            Últimas Transações Mercado Pago
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
                    <th class="p-3 text-left">Ref</th>
                    <th class="p-3 text-left">Data</th>
                </tr>
                </thead>

                <tbody>

                @forelse($payments as $payment)

                    <tr class="border-t dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800">

                        <td class="p-3">
                            {{ $payment['id'] }}
                        </td>

                        <td class="p-3">
                            {{ $payment['payer'] ?? '-' }}
                        </td>

                        <td class="p-3 uppercase">
                            {{ $payment['method'] ?? '-' }}
                        </td>

                        <td class="p-3">
                            @php
                                $status = $payment['status'] ?? '-';
                            @endphp

                            <span class="px-2 py-1 rounded text-xs
                                @if($status === 'approved') bg-green-100 text-green-700
                                @elseif($status === 'pending') bg-yellow-100 text-yellow-700
                                @elseif($status === 'rejected') bg-red-100 text-red-700
                                @else bg-gray-100 text-gray-700
                                @endif
                            ">
                                {{ $status }}
                            </span>
                        </td>

                        <td class="p-3 font-semibold">
                            R$ {{ number_format($payment['value'] ?? 0, 2, ',', '.') }}
                        </td>

                        <td class="p-3">
                            {{ $payment['external'] ?? '-' }}
                        </td>

                        <td class="p-3">
                            {{ !empty($payment['created']) ? \Carbon\Carbon::parse($payment['created'])->format('d/m/Y H:i') : '-' }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="p-5 text-center text-gray-500">
                            Nenhuma transação encontrada.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>
        </div>

    </div>

</x-filament-panels::page>
