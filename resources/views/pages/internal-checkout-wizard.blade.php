<x-filament::page>
    {{ $this->form }}

    {{--@if($this->cppGateways)
        <x-filament::button wire:click="submit" class="mt-4">
            Finalizar Pagamento
        </x-filament::button>
    @endif--}}
</x-filament::page>

@if($this->cppGateways->field_1)
    @push('scripts')
        <script src="https://sdk.mercadopago.com/js/v2"></script>
        {{--<script>
            document.addEventListener('DOMContentLoaded', async () => {
                const mp = new window.MercadoPago("{{ \Illuminate\Support\Facades\Crypt::decrypt($cppGateways->field_1)  }}", {
                    locale: "pt-BR",
                });

                const cardForm = mp.cardForm({
                    amount: '100.00',
                    autoMount: true,
                    form: {
                        id: 'form-checkout',
                        cardNumber: { id: 'cardNumber' },
                        expirationDate: { id: 'cardExpiration' },
                        securityCode: { id: 'cardCVV' },
                        cardholderName: { id: 'cardholderName' },
                        email: { id: 'email' },
                        installments: { id: 'installments' }, // <select id="installments">
                        issuer: { id: 'issuer' },
                    },
                    callbacks: {
                        onFormMounted: function(data) {},
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

            });
        </script>--}}
        <script>
            document.addEventListener('DOMContentLoaded', async () => {
                const mp = new window.MercadoPago("{{ \Illuminate\Support\Facades\Crypt::decrypt($cppGateways->field_1) }}", {
                    locale: "pt-BR",
                });

                // Função que inicializa o cardForm
                const initCardForm = () => {
                    // verifica se os campos existem
                    const form = document.getElementById('form-checkout');
                    const cardNumber = document.getElementById('cardNumber');
                    const expiration = document.getElementById('cardExpiration');
                    const cvv = document.getElementById('cardCVV');
                    const holder = document.getElementById('cardholderName');
                    const email = document.getElementById('email');
                    const installments = document.getElementById('installments');
                    const issuer = document.getElementById('issuer');

                    if (!form || !cardNumber || !expiration || !cvv || !holder || !email || !installments || !issuer) {
                        console.log('Campos do cartão ainda não renderizados');
                        return null;
                    }

                    return mp.cardForm({
                        amount: '100.00',
                        autoMount: true,
                        form: {
                            id: 'form-checkout',
                            cardNumber: { id: 'cardNumber' },
                            expirationDate: { id: 'cardExpiration' },
                            securityCode: { id: 'cardCVV' },
                            cardholderName: { id: 'cardholderName' },
                            email: { id: 'email' },
                            installments: { id: 'installments' },
                            issuer: { id: 'issuer' },
                        },
                        callbacks: {
                            onFormMounted: function() {
                                console.log('CardForm montado');
                            },
                            onBinChange: function(data) {
                                console.log('onBinChange', data);
                            },
                            onSubmit: function(event) {
                                event.preventDefault();
                                const formData = cardForm.getCardFormData();
                                console.log('Token gerado:', formData.token);
                                // @this.call('processarPagamentoCartao', formData.token)
                            },
                        },
                    });
                };

                let cardForm = null;

                // Inicializa quando a aba de cartão for visível
                Livewire.hook('message.processed', (message, component) => {
                    const methodChecked = component.get('method_checked');
                    if (methodChecked == {{ MethodPaymentEnum::credit_card->value }}) {
                        if (!cardForm) {
                            cardForm = initCardForm();
                        }
                    }
                });
            });
        </script>
    @endpush
@endif
