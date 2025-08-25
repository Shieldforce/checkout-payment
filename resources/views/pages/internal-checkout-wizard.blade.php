<x-filament::page>
    <form id="form-checkout-wizard">
        {{ $this->form }}
    </form>
</x-filament::page>

@if($this->cppGateways->field_1)
    @push('scripts')
        <script src="https://sdk.mercadopago.com/js/v2"></script>
        <script>

            document.addEventListener('DOMContentLoaded', async () => {
                const mp = new window.MercadoPago("{{ \Illuminate\Support\Facades\Crypt::decrypt($cppGateways->field_1) }}", {
                    locale: "pt-BR",
                });

                let lastBin = null;

                // Função que inicializa o cardForm
                const initCardForm = () => {
                        // verifica se os campos existem
                        const form = document.getElementById('form-checkout-wizard');
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
                                id: 'form-checkout-wizard',
                                cardNumber: {
                                    id: "cardNumber",
                                    placeholder: '0000 0000 0000 0000',
                                },
                                expirationDate: {
                                    id: 'cardExpiration',
                                    placeholder: 'mm/YY',
                                },
                                securityCode: {
                                    id: 'cardCVV',
                                    placeholder: '345'
                                },
                                cardholderName: {
                                    id: 'cardholderName',
                                    placeholder: 'Fulano da Silva'
                                },
                                email: { id: 'email' },
                                installments: {
                                    id: 'installments',
                                    placeholder: 'Quantidade de parcelas'
                                },
                                issuer: {
                                    id: 'issuer',
                                    placeholder: 'Tipo de cartão'
                                },
                            },
                            callbacks: {
                                onFormMounted: error => {
                                    if (error) return console.warn("Form Mounted handling error: ", error);
                                    console.log("Form mounted");
                                },
                                /*onSubmit: function(event) {
                                    /!*event.preventDefault();
                                    const formData = cardForm.getCardFormData();
                                    console.log('Token gerado:', formData.token);*!/
                                    // @this.call('processarPagamentoCartao', formData.token)
                                },
                                onInstallmentsReceived: function(error, data) {
                                    console.log("onInstallmentsReceived", data);
                                },
                                onIssuersReceived: function(error, data) {
                                    console.log("onIssuersReceived", data);
                                },*/
                                /*onBinChange: function (data) {
                                    console.log("onBinChange:", data);

                                    // só muda se o BIN realmente for diferente
                                    if (data && data.bin && data.bin !== lastBin) {
                                        lastBin = data.bin;
                                        console.log("Novo BIN detectado:", data.bin);
                                    } else {
                                        // ignora, evita resetar o installments
                                        console.log("Ignorado, BIN não mudou de fato");
                                    }
                                },*/
                                onError: function(error) {
                                    console.log("error:", error);
                                }
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
