{{--
<div class="flex flex-col items-center justify-center min-h-[70vh] space-y-6">

    --}}
{{-- Resumo da compra --}}{{--

    <div class="bg-white shadow rounded-xl p-6 w-full max-w-md text-center">
        <h2 class="text-xl font-bold mb-4">Resumo do Pedido</h2>

        <div class="space-y-2 text-gray-700">
            <p><strong>Cliente:</strong> {{ $this->step2->first_name ?? "" }} {{ $this->step2->last_name ?? ""  }}</p>
            <p><strong>Email:</strong> {{ $this->step2->email ?? "" }}</p>
            <p><strong>Valor:</strong> R$ {{ number_format($this->checkout->total_price ?? 0, 2, ',', '.') }}</p>
            <p>
                <strong>Forma de Pagamento:</strong>
                {{ \Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum::from($this->checkout->method_checked ?? 1)->label() }}
            </p>
        </div>

        --}}
{{-- Status atual --}}{{--

        <div class="mt-6">
            <span id="statusLabel"
                  class="px-4 py-2 rounded-full text-sm font-semibold
                         {{ $this->statusCheckout == \Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum::finalizado->value
                            ? 'bg-green-200 text-green-800'
                            : 'bg-yellow-200 text-yellow-800' }}"
            >
                {{ $this->statusCheckout == \Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum::finalizado->value
                    ? 'Pagamento Aprovado'
                    : 'Aguardando Pagamento...' }}
            </span>
        </div>
    </div>

    --}}
{{-- Loading GIF enquanto espera --}}{{--

    @if($this->statusCheckout != \Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum::finalizado->value)
        <div class="flex flex-col items-center space-y-2">
            <img src="https://img.pikbest.com/png-images/20190918/cartoon-snail-loading-loading-gif-animation_2734139.png!f305cw" alt="Aguardando pagamento" class="w-32 h-32">
            <p class="text-gray-500">Estamos aguardando a confirmação do seu pagamento...</p>
        </div>
    @endif

    @if($this->statusCheckout == \Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum::finalizado->value)
        <div class="flex flex-col items-center space-y-2">
            <img src="https://cdn3d.iconscout.com/3d/premium/thumb/aprovado-3d-icon-png-download-11933264.png" alt="Aguardando pagamento" class="w-32 h-32">
        </div>
    @endif

    --}}
{{-- Atualização automática só se estiver no step 5 e ainda não aprovado --}}{{--

    @if(
        isset($this->checkout->startOnStep) &&
        $this->checkout->startOnStep == 5
    )
        <div wire:poll.30s="refreshStatusCheckout"></div>
    @endif

</div>
--}}

<div class="flex flex-col md:flex-row items-stretch justify-center min-h-[60vh] bg-white shadow rounded-xl overflow-hidden">

    {{-- Coluna da esquerda (imagem e loading) --}}
    <div class="flex flex-col items-center justify-center w-full md:w-1/2 bg-gray-50 p-8 border-b md:border-b-0 md:border-r border-gray-200">
        @if($this->statusCheckout != \Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum::finalizado->value)
            <div class="flex flex-col items-center space-y-4">
                <img src="https://img.pikbest.com/png-images/20190918/cartoon-snail-loading-loading-gif-animation_2734139.png!f305cw"
                     alt="Aguardando pagamento" class="w-40 h-40 object-contain">
                <p class="text-gray-500 text-base text-center">
                    Estamos aguardando a confirmação do seu pagamento...
                </p>
            </div>
        @else
            <div class="flex flex-col items-center space-y-4">
                <img src="https://cdn3d.iconscout.com/3d/premium/thumb/aprovado-3d-icon-png-download-11933264.png"
                     alt="Pagamento aprovado" class="w-40 h-40 object-contain">
                <p class="text-green-600 font-semibold text-lg text-center">
                    Pagamento aprovado com sucesso!
                </p>
            </div>
        @endif
    </div>

    {{-- Coluna da direita (resumo) --}}
    <div class="flex flex-col justify-center w-full md:w-1/2 p-8">
        <h2 class="text-2xl font-bold mb-6 text-center md:text-left">Resumo do Pedido</h2>

        <div class="space-y-3 text-gray-700 text-base">
            <p><strong>Cliente:</strong> {{ $this->step2->first_name ?? "" }} {{ $this->step2->last_name ?? ""  }}</p>
            <p><strong>Email:</strong> {{ $this->step2->email ?? "" }}</p>
            <p><strong>Valor:</strong> R$ {{ number_format($this->checkout->total_price ?? 0, 2, ',', '.') }}</p>
            <p>
                <strong>Forma de Pagamento:</strong>
                {{ \Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum::from($this->checkout->method_checked ?? 1)->label() }}
            </p>
        </div>

        {{-- Status atual --}}
        <div class="mt-8 text-center md:text-left">
            <span id="statusLabel"
                  class="px-5 py-2 rounded-full text-sm font-semibold
                         {{ $this->statusCheckout == \Shieldforce\CheckoutPayment\Enums\StatusCheckoutEnum::finalizado->value
                            ? 'bg-green-200 text-green-800'
                            : 'bg-yellow-200 text-yellow-800' }}">
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
