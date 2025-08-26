<x-filament::page>
    <form id="form-checkout-wizard">
        {{ $this->form }}
    </form>

    {{--type="submit"--}} {{--wire:click="submit"--}}
    {{--onclick="document.getElementById('form-checkout-wizard').dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}));"--}}
    <!--onclick="document.getElementById('form-checkout-wizard').requestSubmit()"-->
</x-filament::page>

@if($this->cppGateways->field_1)
    @push('scripts')

        <script src="https://sdk.mercadopago.com/js/v2"></script>
        <script>
            document.addEventListener('DOMContentLoaded', async () => {
                var mp = null;

                const btn = document.querySelector('#btn-next-step')
                btn.type = 'button'
                btn.textContent = 'Confirmar Pagamento'
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                btn.classList.add('disabled');


                // Função que inicializa o cardForm
                const initCardForm = () => {
                    mp = new window.MercadoPago("{{ \Illuminate\Support\Facades\Crypt::decrypt($cppGateways->field_1) }}", {
                        locale: 'pt-BR',
                    })

                    let submitOff = true

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

                                btn.textContent = 'Confirmar Pagamento'
                                btn.disabled = false;
                                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                                btn.classList.remove('disabled');

                                // força o autofocus
                                setTimeout(() => {
                                    const cardNumber = document.getElementById('cardNumber')
                                    cardNumber.focus()
                                }, 200)
                            },
                            onSubmit: function(event) {
                                console.log('onSubmit:', event)

                                if(submitOff) {
                                    submitOff = false;
                                    event.preventDefault();
                                    event.stopImmediatePropagation();

                                    btn.textContent = 'Próximo'
                                    btn.disabled = false;
                                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                                    btn.classList.remove('disabled');
                                    btn.type = 'submit'
                                    btn.id = null

                                    var msg = 'Agora só esperar que avisaremos quando o pagamento for aprovado! ';
                                    msg += 'Pode ser por e-mail, whatsapp ou sms, fique ligado(a). ';
                                    msg += 'Agora só clicar em próximo para finalizar o checkout!';
                                    window.Livewire.dispatch('show-notification', {
                                        title: 'Oba, deu certo!',
                                        body: msg,
                                        status: 'success'
                                    });
                                    return;
                                }

                                if(submitOff === false) {
                                    window.Livewire.dispatch('go-to-step', { step: 5 });
                                }
                            },
                            onPaymentMethodsReceived: function(error, data) {
                                console.log('onPaymentMethodsReceived:', error, data)

                                const imgBrandCard = document.getElementById('img-brand-card')
                                imgBrandCard.src = data[0].thumbnail ?? 'https://storage.googleapis.com/star-lab/blog/OGs/image-not-found.png'

                                const issuerNameCard = document.getElementById('issuer-name-card')
                                issuerNameCard.textContent = data[0].issuer.name ?? 'Meu Cartão'
                            },
                            onCardTokenReceived: function(error, data) {
                                if(data.token) {
                                    document.getElementById("cardToken").value = data.token;
                                    window.Livewire.dispatch('update-card-token', { cardToken: data.token });
                                }
                            },
                            onError: function(errors) {
                                console.log('Erro do MP:', errors);

                                const fieldMap = {
                                    cardNumber: 'cardNumber',
                                    cardholderName: 'cardholderName',
                                    securityCode: 'cardCVV',
                                    expirationMonth: 'cardExpiration',
                                    expirationYear: 'cardExpiration',
                                    email: 'email',
                                };

                                const camposComErro = new Set();

                                errors.forEach(err => {
                                    let field = null;

                                    if (err.field && fieldMap[err.field]) {
                                        field = fieldMap[err.field];
                                    } else {
                                        if (err.message.includes('cardNumber')) field = 'cardNumber';
                                        if (err.message.includes('expirationMonth') || err.message.includes('expirationYear')) field = 'cardExpiration';
                                        if (err.message.includes('securityCode')) field = 'cardCVV';
                                        if (err.message.includes('cardholderName')) field = 'cardholderName';
                                        if (err.message.includes('email')) field = 'email';
                                    }

                                    if (field && !camposComErro.has(field)) {
                                        camposComErro.add(field); // marca que já tratamos esse campo

                                        const input = document.getElementById(field);
                                        if (input) {
                                            // remove erro antigo se houver
                                            let errorEl = input.closest('.filament-forms-field-wrapper')?.querySelector('.mp-error');
                                            if (errorEl) errorEl.remove();

                                            // cria novo span
                                            errorEl = document.createElement('span');
                                            errorEl.classList.add('mp-error');
                                            errorEl.style.color = 'red';
                                            errorEl.style.fontSize = '12px';
                                            errorEl.style.display = 'block';
                                            errorEl.style.marginTop = '4px';
                                            errorEl.textContent = traduzMensagem(err.message, field);

                                            input.insertAdjacentElement('afterend', errorEl);
                                        }
                                    }
                                });

                                function traduzMensagem(msg, field) {
                                    const traducoes = {
                                        "cardNumber": "Número do cartão inválido.",
                                        "expirationMonth": "Mês de validade inválido.",
                                        "expirationYear": "Ano de validade inválido.",
                                        "cardExpiration": "Data de validade inválida.",
                                        "securityCode": "Código de segurança inválido.",
                                        "cardholderName": "Nome do titular inválido.",
                                        "identificationNumber": "Documento inválido.",
                                    };

                                    return traducoes[field] || "Erro ao validar os dados.";
                                }
                            },

                        },
                    })

                }

                let cardForm = null

                // Inicializa quando a aba de cartão for visível
                document.getElementById('method_checked').addEventListener('change', function(event) {
                    mp = null;

                    btn.textContent = 'Confirmar Pagamento'
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    btn.classList.remove('disabled');

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
                                }

                                btn.addEventListener('click', bloquearAvanco)
                            }, 500)
                        }
                    }
                })
            })
        </script>
    @endpush
@endif
