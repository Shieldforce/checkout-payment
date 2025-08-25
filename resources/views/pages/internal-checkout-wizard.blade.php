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
                /*const btn = document.querySelector('#btn-next-step');
                if (btn) {
                    btn.addEventListener('click', async (event) => {
                        event.preventDefault();
                        event.stopImmediatePropagation();
                        console.log("Agora sim segurei!");
                    }, true);
                }*/

                const mp = new window.MercadoPago("{{ \Illuminate\Support\Facades\Crypt::decrypt($cppGateways->field_1) }}", {
                    locale: "pt-BR",
                });

                let lastBin = null;

                // Função que inicializa o cardForm
                const initCardForm = () => {
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

                                // força o autofocus
                                setTimeout(() => {
                                    const cardNumber = document.getElementById('cardNumber');
                                    cardNumber.focus();
                                }, 200);
                            },
                            onSubmit: function(event) {
                                console.log("onSubmit:", event);

                                /*event.preventDefault();
                                const formData = cardForm.getCardFormData();
                                console.log('Token gerado:', formData.token);*/
                                // @this.call('processarPagamentoCartao', formData.token)
                            },
                            onIdentificationTypesReceived: function(error, data) {
                                console.log("onIdentificationTypesReceived:", error, data);
                            },
                            onPaymentMethodsReceived: function(error, data) {
                                console.log("onPaymentMethodsReceived:", error, data);

                                const imgBrandCard = document.getElementById("img-brand-card");
                                imgBrandCard.src = data[0].thumbnail ?? "https://storage.googleapis.com/star-lab/blog/OGs/image-not-found.png";

                                const issuerNameCard = document.getElementById("issuer-name-card");
                                issuerNameCard.textContent = data[0].issuer.name ?? "Meu Cartão";
                            },
                            onFetching: function(error, data) {
                                console.log("onFetching:", error, data);
                            },
                            onValidityChange: function(error, data) {
                                console.log("onValidityChange:", error, data);
                            },
                            onInstallmentsReceived: function(error, data) {
                                console.log("onInstallmentsReceived:", error, data);
                            },
                            onIssuersReceived: function(error, data) {
                                console.log("onIssuersReceived:", error, data);
                            },
                            onBinChange: function (data) {
                                console.log("onBinChange:", data);

                                // só muda se o BIN realmente for diferente
                                if (data && data.bin && data.bin !== lastBin) {
                                    lastBin = data.bin;
                                    console.log("Novo BIN detectado:", data.bin);
                                } else {
                                    // ignora, evita resetar o installments
                                    console.log("Ignorado, BIN não mudou de fato");
                                }
                            },
                            onCardTokenReceived: function(error, data) {
                                console.log("onCardTokenReceived:", error, data);
                            },
                            onError: function(error) {
                                console.log("error:", error);
                            }
                        },
                    });

                };

                let cardForm = null;

                // Inicializa quando a aba de cartão for visível
                document.getElementById("method_checked").addEventListener("change", function(event) {
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
