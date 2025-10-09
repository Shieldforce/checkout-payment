<div class="p-6 text-center">
    <h2 class="text-lg font-bold text-red-600">
        Nenhum gateway ativo encontrado!
    </h2>
    <p class="mt-2 text-gray-600">
        Para usar o checkout, você precisa cadastrar um gateway de pagamento.
    </p>

    @if(auth()->check())
        @php
            $routeName = \Shieldforce\CheckoutPayment\Pages\CPPGatewaysPage::getRouteName()
        @endphp
        <br>
        <hr>
        <br>
        <a href="{{ route( $routeName ) }}"
           class="mt-4 inline-block px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
            Ir para lista de gateways
        </a>
    @endif
</div>
