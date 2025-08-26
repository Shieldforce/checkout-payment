<x-filament::page>
    <form id="form-checkout-wizard">
        {{ $this->form }}
    </form>

    {{--@if($this->checkout->startOnStep == 4)
        <x-filament::button
            --}}{{--type="submit"--}}{{--
            --}}{{--wire:click="submit"--}}{{--
            --}}{{--onclick="document.getElementById('form-checkout-wizard').dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}));"--}}{{--
            --}}{{--onclick="formSubmit.submit()"--}}{{--
            --}}{{--onclick="formSubmit.requestSubmit()"--}}{{--
            type="button"
            onclick="document.getElementById('form-checkout-wizard').requestSubmit()"
            class="mt-4"
        >
            Finalizar Pagamento
        </x-filament::button>
    @endif--}}
</x-filament::page>

@if($this->cppGateways->field_1)
    @push('scripts')
        <script src="https://sdk.mercadopago.com/js/v2"></script>
        <script>
            document.addEventListener('DOMContentLoaded', async () => {

                const btn = document.querySelector('#btn-next-step')
                btn.type = 'button'
                btn.textContent = 'Confirmar Pagamento'
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                btn.classList.add('disabled');

                const mp = new window.MercadoPago("{{ \Illuminate\Support\Facades\Crypt::decrypt($cppGateways->field_1) }}", {
                    locale: 'pt-BR',
                })

                let lastBin = null

                // Função que inicializa o cardForm
                const initCardForm = () => {
                    return mp.cardForm({
                        amount: '100.00',
                        autoMount: true,
                        form: {
                            id: 'form-checkout-wizard',
                            cardNumber: {
                                id: 'cardNumber',
                                placeholder: '0000 0000 0000 0000',
                            },
                            expirationDate: {
                                id: 'cardExpiration',
                                placeholder: 'mm/YY',
                            },
                            securityCode: {
                                id: 'cardCVV',
                                placeholder: '345',
                            },
                            cardholderName: {
                                id: 'cardholderName',
                                placeholder: 'Fulano da Silva',
                            },
                            email: { id: 'email' },
                            installments: {
                                id: 'installments',
                                placeholder: 'Quantidade de parcelas',
                            },
                            issuer: {
                                id: 'issuer',
                                placeholder: 'Tipo de cartão',
                            },
                        },
                        callbacks: {
                            onFormMounted: error => {
                                if (error) return console.warn('Form Mounted handling error: ', error)
                                console.log('Form mounted')

                                // força o autofocus
                                setTimeout(() => {
                                    const cardNumber = document.getElementById('cardNumber')
                                    cardNumber.focus()
                                }, 200)
                            },
                            onSubmit: function(event) {
                                console.log('onSubmit:', event)

                                event.preventDefault()
                                event.stopImmediatePropagation()

                                /*event.preventDefault();
                                const formData = cardForm.getCardFormData();
                                console.log('Token gerado:', formData.token);*/
                                // @this.call('processarPagamentoCartao', formData.token)
                            },
                            onIdentificationTypesReceived: function(error, data) {
                                console.log('onIdentificationTypesReceived:', error, data)
                            },
                            onPaymentMethodsReceived: function(error, data) {
                                console.log('onPaymentMethodsReceived:', error, data)

                                const imgBrandCard = document.getElementById('img-brand-card')
                                imgBrandCard.src = data[0].thumbnail ?? 'https://storage.googleapis.com/star-lab/blog/OGs/image-not-found.png'

                                const issuerNameCard = document.getElementById('issuer-name-card')
                                issuerNameCard.textContent = data[0].issuer.name ?? 'Meu Cartão'
                            },
                            onFetching: function(error, data) {
                                console.log('onFetching:', error, data)
                            },
                            onValidityChange: function(error, data) {
                                if (data === 'securityCode') {
                                    console.log('deu certo')
                                }
                            },
                            onInstallmentsReceived: function(error, data) {
                                console.log('onInstallmentsReceived:', error, data)
                            },
                            onIssuersReceived: function(error, data) {
                                console.log('onIssuersReceived:', error, data)
                            },
                            onBinChange: function(data) {
                                console.log('onBinChange:', data)

                                // só muda se o BIN realmente for diferente
                                if (data && data.bin && data.bin !== lastBin) {
                                    lastBin = data.bin
                                    console.log('Novo BIN detectado:', data.bin)
                                } else {
                                    // ignora, evita resetar o installments
                                    console.log('Ignorado, BIN não mudou de fato')
                                }
                            },
                            onCardTokenReceived: function(error, data) {
                                console.log('onCardTokenReceived:', error, data)
                            },
                            onError: function(errors) {
                                console.log('Erro do MP:', errors);

                                const fieldMap = {
                                    cardNumber: 'card_number',
                                    cardholderName: 'card_payer_name',
                                    securityCode: 'card_cvv',
                                    expirationMonth: 'cardExpiration',
                                    expirationYear: 'cardExpiration',
                                    email: 'email',
                                };

                                errors.forEach(err => {
                                    let field = null;

                                    // tenta mapear pelo campo
                                    if (err.field && fieldMap[err.field]) {
                                        field = fieldMap[err.field];
                                    } else {
                                        // deduz pelo message
                                        if (err.message.includes('cardNumber')) field = 'card_number';
                                        if (err.message.includes('expirationMonth') || err.message.includes('expirationYear')) field = 'cardExpiration';
                                        if (err.message.includes('securityCode')) field = 'card_cvv';
                                        if (err.message.includes('cardholderName')) field = 'card_payer_name';
                                        if (err.message.includes('email')) field = 'email';
                                    }

                                    if (field) {
                                        // aqui emitimos para o Livewire
                                        Livewire.emit('setCardError', field, err.message);
                                    }
                                });
                            },
                        },
                    })

                }

                let cardForm = null

                // Inicializa quando a aba de cartão for visível
                document.getElementById('method_checked').addEventListener('change', function(event) {
                    const valueSelectMethodCheck = parseInt(event.target.value)
                    const creditCardEnum = parseInt("{{ \Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum::credit_card->value }}")

                    if (valueSelectMethodCheck === creditCardEnum) {
                        if (!cardForm) {
                            setTimeout(function() {
                                cardForm = initCardForm()

                                btn.textContent = 'Confirmar Pagamento'
                                btn.disabled = false;
                                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                                btn.classList.remove('disabled');

                                function bloquearAvanco(event) {
                                    event.preventDefault()
                                    event.stopImmediatePropagation()
                                    document.getElementById('form-checkout-wizard').requestSubmit()
                                    console.log('chegou')
                                }

                                btn.addEventListener('click', bloquearAvanco)
                            }, 2000)
                        }
                    }
                })
            })
        </script>
    @endpush
@endif
