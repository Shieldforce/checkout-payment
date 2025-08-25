<div
    {{--x-data="{
        card_number: @entangle('card_number').live,
        card_payer_name: @entangle('card_payer_name').live,
        card_validate: @entangle('card_validate').live,
        card_cvv: @entangle('card_cvv').live,
        formatNumber(num) {
            if (!num) return '0000 0000 0000 0000'
            return num.replace(/\D/g, '')
                .replace(/(\d{4})(?=\d)/g, '$1 ')
                .trim()
        }
    }"--}}
    class="relative w-80 h-48 rounded-xl shadow-lg
           bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500
           text-black dark:text-white
           p-5 select-none rounded-xl"
>
    <!-- Header -->
    <div class="flex justify-between items-center">
        <span class="text-sm font-semibold">Meu Cartão</span>
        <span class="text-xs font-bold">VISA</span>
    </div>

    <!-- Número do cartão -->
    <div class="mt-8 text-xl tracking-widest font-mono">
        {{--<span x-text="formatNumber(card_number)"></span>--}}
    </div>

    <!-- Rodapé -->
    <div class="flex justify-between items-center mt-6">
        <div>
            <div class="text-xs opacity-80">Nome</div>
            <div
                class="uppercase tracking-wide text-sm font-semibold"
                 {{--x-text="card_payer_name || 'NOME DO TITULAR'"--}}
            >

            </div>
        </div>
        <div>
            <div class="text-xs opacity-80">Validade</div>
            <div
                class="tracking-wide font-semibold"
                {{--x-text="card_validate || 'MM/AA'"--}}
            >

            </div>
        </div>
    </div>
</div>

@php
    use Shieldforce\CheckoutPayment\Models\CppGateways;
    use Shieldforce\CheckoutPayment\Enums\TypeGatewayEnum;
    $cppGateways = CppGateways::where("name", TypeGatewayEnum::mercado_pago->value)
            ->where("active", true)
            ->first();
@endphp

@if($cppGateways->field_2)
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script>

        /*const mp = new MercadoPago("{{ $cppGateways->field_2 ?? null  }}")*/

        const mp = new window.MercadoPago("{{ $cppGateways->field_2 ?? null  }}", {
            locale: "pt-BR",
        });

        const paymentMethods = await mp.getPaymentMethods({ bin: "41111111" });

        console.log(paymentMethods);

        const cardForm = mp.cardForm({
            amount: '100.00',
            autoMount: true,
            form: {
                cardNumber: { id: 'cardNumber' },
                expirationDate: { id: 'cardExpiration' },
                securityCode: { id: 'cardCVV' },
                cardholderName: { id: 'cardholderName' },
                email: { id: 'email' },
            },
            callbacks: {
                /*onSubmit: async (event) => {
                    event.preventDefault()
                    const token = await mp.createCardToken({
                        cardNumber: document.getElementById('cardNumber').value,
                        expirationMonth: ...,
                        expirationYear: ...,
                        securityCode: ...,
                        cardholderName: ...,
                    })
                    // enviar token para o backend
                },*/
                onBinChange: function(data) {
                    console.log(data)

                    // se quiser, pode jogar pro Livewire
                    //@this.set('card_brand', data.paymentMethod?.id)
                },

                onSubmit: function(event) {
                    event.preventDefault()

                    const formData = cardForm.getCardFormData()
                    console.log('Token gerado:', formData.token)

                    //@this.call('processarPagamentoCartao', formData.token)
                },
            },
        })

    </script>
@endif
