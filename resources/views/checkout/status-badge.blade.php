{{--
<div class="flex flex-col md:flex-row items-stretch justify-center min-h-[60vh] bg-white shadow rounded-xl overflow-hidden">

    --}}
{{-- Coluna da esquerda (imagem e loading) --}}{{--

    <div class="flex flex-col items-center justify-center w-full md:w-1/2 bg-gray-50 p-8 border-b md:border-b-0 md:border-r border-gray-200">
        @if($this->statusCheckout != \Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum::finalizado->value)
            <div class="flex flex-col items-center space-y-4">
                <img src="https://img.pikbest.com/png-images/20190918/cartoon-snail-loading-loading-gif-animation_2734139.png!f305cw"
                     alt="Aguardando pagamento" class="w-40 h-40 object-contain">
            </div>
        @else
            <div class="flex flex-col items-center space-y-4">
                <img width="150" height="150" src="https://cdn3d.iconscout.com/3d/premium/thumb/aprovado-3d-icon-png-download-11933264.png"
                     alt="Pagamento aprovado" class="w-40 h-40 object-contain">
            </div>
        @endif
    </div>

    --}}
{{-- Coluna da direita (resumo) --}}{{--

    <div class="flex flex-col justify-left w-full md:w-1/2 p-8" style="border-left: 1px dashed #cecece; padding: 30px;">

        <h2 class="text-2xl font-bold mb-6 text-left md:text-left">Resumo do Pedido</h2>

        <hr>
        <br>

        <div class="space-y-3 text-gray-700 text-base">
            <p><strong>Cliente:</strong> {{ $this->step2->first_name ?? "" }} {{ $this->step2->last_name ?? ""  }}</p>
            <p><strong>Email:</strong> {{ $this->step2->email ?? "" }}</p>
            <p>
                <strong>Valor:</strong>
                R$ {{ isset($this->checkout->total_price)
                    ? number_format($this->checkout->total_price ?? 0, 2, ',', '.')
                    : "..." }}
            </p>
            <p>
                <strong>Forma de Pagamento:</strong>
                {{ \Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum::from($this->checkout->method_checked ?? 1)->label() }}
            </p>
        </div>

        --}}
{{-- Status atual --}}{{--

        <div class="mt-8 text-left md:text-left">
            <span
                style="{{ $this->statusCheckout == \Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum::finalizado->value
                            ? 'color: green;'
                            : 'color: orange;' }}"
                id="statusLabel"
                  class="px-5 py-2 rounded-full text-sm font-semibold">
                {{ $this->statusCheckout == \Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum::finalizado->value
                    ? 'Pagamento Aprovado'
                    : 'Aguardando Pagamento...' }}
            </span>
        </div>
    </div>

    --}}
{{-- Atualização automática --}}{{--

    @if(isset($this->checkout->startOnStep) && $this->checkout->startOnStep == 5)
        <div wire:poll.30s="refreshStatusCheckout"></div>
    @endif
</div>

@php
    $attempts = json_decode($this->checkout->return_gateway ?? '[]', true);
@endphp

@if(!empty($attempts))

    @dd($attempts);

    <hr>

    <tbody class="bg-white divide-y divide-gray-100">
    @foreach($attempts as $i => $attempt)
        <tr>
            <td class="px-6 py-4 text-sm text-gray-700">{{ $i+1 }}</td>
            <td class="px-6 py-4 text-sm text-gray-700">
                {{ ucfirst($attempt['method'] ?? '-') }}
            </td>
            <td class="px-6 py-4 text-sm">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        @if(($attempt['status'] ?? '') === 'approved') bg-green-100 text-green-700
                        @elseif(($attempt['status'] ?? '') === 'rejected') bg-red-100 text-red-700
                        @else bg-orange-100 text-orange-700 @endif">
                        {{ ucfirst($attempt['status'] ?? '-') }}
                    </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
                {{ isset($attempt['data']['date_created'])
                    ? \Carbon\Carbon::parse($attempt['data']['date_created'])->format('d/m/Y H:i')
                    : '-' }}
            </td>
        </tr>
    @endforeach
    </tbody>
@else
    <tbody>
        <tr>
            <td colspan="4" class="text-center text-gray-500 py-6">
                Nenhuma tentativa de pagamento encontrada.
            </td>
        </tr>
    </tbody>
@endif

--}}

<div class="flex flex-col md:flex-row items-stretch justify-center min-h-[60vh] bg-white dark:bg-gray-800 shadow rounded-xl overflow-hidden">

    {{-- Coluna da esquerda (imagem e loading) --}}
    <div class="flex flex-col items-center justify-center w-full md:w-1/2 bg-gray-50 dark:bg-gray-900 p-8 border-b md:border-b-0 md:border-r border-gray-200 dark:border-gray-700">
        @if($this->statusCheckout != \Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum::finalizado->value)
            <div class="flex flex-col items-center space-y-4">
                <img src="https://img.pikbest.com/png-images/20190918/cartoon-snail-loading-loading-gif-animation_2734139.png!f305cw"
                     alt="Aguardando pagamento" class="w-40 h-40 object-contain">
            </div>
        @else
            <div class="flex flex-col items-center space-y-4">
                <img width="150" height="150" src="https://cdn3d.iconscout.com/3d/premium/thumb/aprovado-3d-icon-png-download-11933264.png"
                     alt="Pagamento aprovado" class="w-40 h-40 object-contain">
            </div>
        @endif
    </div>

    {{-- Coluna da direita (resumo) --}}
    <div class="flex flex-col justify-left w-full md:w-1/2 p-8" style="border-left: 1px dashed #cecece; padding: 30px;">

        <h2 class="text-2xl font-bold mb-6 text-left md:text-left text-gray-900 dark:text-gray-100">Resumo do Pedido</h2>

        <hr class="border-gray-300 dark:border-gray-600 mb-4">

        <div class="space-y-3 text-gray-700 dark:text-gray-300 text-base">
            <p><strong>Cliente:</strong> {{ $this->step2->first_name ?? "" }} {{ $this->step2->last_name ?? ""  }}</p>
            <p><strong>Email:</strong> {{ $this->step2->email ?? "" }}</p>
            <p>
                <strong>Valor:</strong>
                R$ {{ isset($this->checkout->total_price)
                    ? number_format($this->checkout->total_price ?? 0, 2, ',', '.')
                    : "..." }}
            </p>
            <p>
                <strong>Forma de Pagamento:</strong>
                {{ \Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum::from($this->checkout->method_checked ?? 1)->label() }}
            </p>
        </div>

        {{-- Status atual --}}
        <div class="mt-8 text-left md:text-left">
            <span
                class="px-5 py-2 rounded-full text-sm font-semibold
                    {{ $this->statusCheckout == \Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum::finalizado->value
                        ? 'bg-green-100 text-green-700 dark:bg-green-700 dark:text-green-100'
                        : 'bg-orange-100 text-orange-700 dark:bg-orange-700 dark:text-orange-100' }}">
                {{ $this->statusCheckout == \Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum::finalizado->value
                    ? 'Pagamento Aprovado'
                    : 'Aguardando Pagamento...' }}
            </span>
        </div>
    </div>

    {{-- Atualização automática --}}
    @if(isset($this->checkout->startOnStep) && $this->checkout->startOnStep == 5)
        <div wire:poll.30s="refreshStatusCheckout"></div>
    @endif
</div>

@php
    $attempts = json_decode($this->checkout->return_gateway ?? '[]', true);
@endphp

@if(isset($attempts) && count($attempts) > 0)
    {{-- Histórico de Tentativas --}}
    <div class="flex flex-col md:flex-row items-stretch justify-center min-h-[60vh] bg-white dark:bg-gray-800 shadow rounded-xl ">
        <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Histórico de Tentativas de Pagamento</h3>

        <div class="overflow-x-auto rounded-lg shadow border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">#</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Forma</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Status</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Data</th>
                </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($attempts as $i => $attempt)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $i+1 }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ ucfirst($attempt['method'] ?? '-') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                @if(($attempt['status'] ?? '') === 'approved') bg-green-100 text-green-700 dark:bg-green-700 dark:text-green-100
                                @elseif(($attempt['status'] ?? '') === 'rejected') bg-red-100 text-red-700 dark:bg-red-700 dark:text-red-100
                                @else bg-orange-100 text-orange-700 dark:bg-orange-700 dark:text-orange-100 @endif">
                                {{ ucfirst($attempt['status'] ?? '-') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ isset($attempt['data']['date_created'])
                                ? \Carbon\Carbon::parse($attempt['data']['date_created'])->format('d/m/Y H:i')
                                : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-gray-500 dark:text-gray-400 py-6">
                            Nenhuma tentativa de pagamento encontrada.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
