<x-filament::page>
    <form id="form-checkout-wizard">
        {{ $this->form }}
    </form>

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
                        const wizardForm1 = document.querySelector('form[id^="form-internal-checkout-wizard"]');
                        const wizardForm = document.querySelector('form[id^="form-"]'); // pega o form do Wizard

                        // verifica se os campos existem
                        const form = document.getElementById('form-checkout-wizard');
                        const cardNumber = document.getElementById('cardNumber');
                        const expiration = document.getElementById('cardExpiration');
                        const cvv = document.getElementById('cardCVV');
                        const holder = document.getElementById('cardholderName');
                        const email = document.getElementById('email_card');
                        const installments = document.getElementById('installments');
                        const issuer = document.getElementById('issuer');

                        console.log({
                            wizardForm1,
                            wizardForm,
                            form,
                            cardNumber,
                            expiration,
                            cvv,
                            holder,
                            email,
                            installments,
                            issuer,
                        });

                        if (!form || !cardNumber || !expiration || !cvv || !holder || !email || !installments || !issuer) {
                            console.log('Campos do cartão ainda não renderizados');
                            return null;
                        }

                        return mp.cardForm({
                            amount: '100.00',
                            autoMount: true,
                            form: {
                                id: 'form-checkout-wizard',
                                cardNumber: { id: 'cardNumber' },
                                expirationDate: { id: 'cardExpiration' },
                                securityCode: { id: 'cardCVV' },
                                cardholderName: { id: 'cardholderName' },
                                email: { id: 'email_card' },
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
                document.getElementById("method_checked").addEventListener("change", function(event) {
                    console.log(event.target.value);
                    const valueSelectMethodCheck = parseInt(event.target.value);
                    const creditCardEnum = parseInt("{{ \Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum::credit_card->value }}");

                    if (valueSelectMethodCheck === creditCardEnum) {
                        if (!cardForm) {
                            setTimeout(function() {
                                cardForm = initCardForm();
                            }, 2000)
                        }
                    }
                })
            });
        </script>
    @endpush
@endif
