@php
    $enumStatus = \Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum::finalizado->value
@endphp

<div class="flex flex-col items-center justify-center min-h-[70vh] space-y-6">

    {{-- Resumo da compra --}}
    <div class="bg-white shadow rounded-xl p-6 w-full max-w-md text-center">
        <h2 class="text-xl font-bold mb-4">Resumo do Pedido</h2>

        <div class="space-y-2 text-gray-700">
            <p><strong>Cliente:</strong> {{ $this->step2->first_name }} {{ $this->step2->last_name  }}</p>
            <p><strong>Email:</strong> {{ $this->step2->email }}</p>
            <p><strong>Valor:</strong> R$ {{ number_format($this->checkout->total_price, 2, ',', '.') }}</p>
            <p>
                <strong>Forma de Pagamento:</strong>
                {{ \Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum::from($this->checkout->method_checked)->label() }}
            </p>
        </div>

        {{-- Status atual --}}
        <div class="mt-6">
            <span id="statusLabel"
                  class="px-4 py-2 rounded-full text-sm font-semibold
                         {{ $this->refreshStatusCheckout == \Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum::finalizado->value
                            ? 'bg-green-200 text-green-800'
                            : 'bg-yellow-200 text-yellow-800' }}"
            >
                {{ $this->refreshStatusCheckout == \Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum::finalizado->value
                    ? 'Pagamento Aprovado'
                    : 'Aguardando Pagamento...' }}
            </span>
        </div>
    </div>

    {{-- Loading GIF enquanto espera --}}
    @if($this->refreshStatusCheckout != \Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum::finalizado->value)
        <div class="flex flex-col items-center space-y-2">
            <img src="{{ asset('images/payment-loading.gif') }}" alt="Aguardando pagamento" class="w-32 h-32">
            <p class="text-gray-500">Estamos aguardando a confirmação do seu pagamento...</p>
        </div>
    @endif

    {{-- Atualização automática só se estiver no step 5 e ainda não aprovado --}}
    @if(
        $this->checkout->startOnStep == 5 &&
        $this->refreshStatusCheckout != \Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum::finalizado->value
    )
        <div wire:poll.30s="refreshStatusCheckout"></div>
    @endif

</div>
