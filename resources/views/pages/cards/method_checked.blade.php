@php
    $opcoes = [
    [
        'id' => 1,
        'titulo' => 'Cartão',
        'icone' => 'heroicon-o-credit-card',
        'descricao' => 'Pagamento no Cartão de Crédito',
    ],
    [
        'id' => 2,
        'titulo' => 'Pix',
        'icone' => 'heroicon-o-qr-code',
        'descricao' => 'Pagamento no Pix',
    ],
    [
        'id' => 3,
        'titulo' => 'Boleto',
        'icone' => 'heroicon-o-document-text',
        'descricao' => 'Pagamento no Boleto',
    ],
];

    $selecionado = $getState();
    $tipoUrl = request()->query('tipo');
@endphp

<div
    x-data="{
        selecionado: Number(@entangle($getStatePath())),
        init() {
            // se a URL tiver o parâmetro tipo, já seleciona automaticamente
            const tipoUrl = Number(new URL(window.location.href).searchParams.get('tipo'));
            if (tipoUrl && !this.selecionado) {
                this.selecionado = tipoUrl;
            }
        }
    }"
    x-init="init()"
    class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4"
>
    @foreach ($opcoes as $opcao)
        <div
            @click="
                selecionado = {{ $opcao['id'] }};
                metodoGo({{ $opcao['id'] }});
            "
            :class="selecionado == @js($opcao['id'])
                ? 'bg-primary-500 text-white dark:bg-primary-600 dark:text-white shadow-lg ring-2 ring-primary-400 border-transparent'
                : 'bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 hover:border-primary-400 hover:bg-primary-50 dark:hover:bg-gray-700'"
            class="cursor-pointer rounded-2xl border-2 p-6 transition-all duration-200"
        >
            <div class="flex flex-col items-center justify-center text-center space-y-2">
                <x-dynamic-component :component="$opcao['icone']" class="w-10 h-10 text-primary-600"/>
                <div class="font-semibold text-lg">{{ $opcao['titulo'] }}</div>
                <p class="text-sm">{{ $opcao['descricao'] }}</p>
            </div>
        </div>
    @endforeach
</div>

@if (!$selecionado)
    <p class="text-center text-gray-400 mt-2 text-sm italic">
        Clique em uma das opções acima para fazer o pagamento.
    </p>
@endif

@push('scripts')
    <script src="https://sdk.mercadopago.com/js/v2"></script>

    <script>
        /*Key mp*/
        const accessKey = "{{ \Illuminate\Support\Facades\Crypt::decrypt($cppGateways->field_1) }}";

        const valor = "{{ $this->checkout->total_price }}";

        const btn = document.querySelector('#btn-next-step');

        {{--Regra para cartao de credito do mercado pago--------------------------------------}}
        const initCardForm = () => {

            const mp = new window.MercadoPago(accessKey, {
                locale: 'pt-BR'
            })

            let submitOff = true

            var paymentMethodId = null

            var installments = 1

            if(!valor) {
                var msgError = 'Valor de pagamento não informado!'
                window.Livewire.dispatch('show-notification', {
                    title: 'Ops, Error!',
                    body: msgError,
                    status: 'danger',
                })

                return;
            }

            return mp.cardForm({
                amount: valor,
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
                        btn.disabled = false
                        btn.classList.remove('opacity-50', 'cursor-not-allowed')
                        btn.classList.remove('disabled')

                        // força o autofocus
                        setTimeout(() => {
                            const cardNumber = document.getElementById('cardNumber')
                            cardNumber.focus()
                        }, 200)
                    },
                    onSubmit: function(event) {
                        console.log('onSubmit:', event)

                        if (submitOff) {
                            submitOff = false
                            event.preventDefault()
                            event.stopImmediatePropagation()

                            btn.textContent = 'Próximo'
                            btn.disabled = false
                            btn.classList.remove('opacity-50', 'cursor-not-allowed')
                            btn.classList.remove('disabled')
                            btn.type = 'submit'
                            btn.id = null

                            var msg = 'Agora só esperar, que avisaremos quando o pagamento for aprovado! '
                            msg += 'Pode ser por e-mail, whatsapp, sms ou até mesmo na próxima tela, fique ligado(a). '
                            msg += 'Agora só clicar em próximo para finalizar o checkout e confirmar todas as informações!'
                            window.Livewire.dispatch('show-notification', {
                                title: 'Legal, falta só um pouquinho!',
                                body: msg,
                                status: 'warning',
                            })

                            installments = document.getElementById("installments").value

                            if(installments !== 1) {
                                window.Livewire.dispatch('go-installments', { installments: installments });
                            }

                            if(paymentMethodId) {
                                window.Livewire.dispatch('payment-method-id', { paymentMethodId: paymentMethodId });
                            }

                            return
                        }

                        if (submitOff === false) {
                            window.Livewire.dispatch('go-to-step', { step: 5 })
                        }
                    },
                    onPaymentMethodsReceived: function(error, data) {
                        console.log('onPaymentMethodsReceived:', error, data)

                        const imgBrandCard = document.getElementById('img-brand-card')
                        imgBrandCard.src = data[0].thumbnail ?? 'https://storage.googleapis.com/star-lab/blog/OGs/image-not-found.png'

                        const issuerNameCard = document.getElementById('issuer-name-card')
                        issuerNameCard.textContent = data[0].issuer.name ?? 'Meu Cartão'

                        if (data[0].payment_type_id) {
                            //paymentMethodId = data[0].payment_type_id
                            paymentMethodId = data[0].id
                        }
                    },
                    onCardTokenReceived: function(error, data) {
                        if (data.token) {
                            document.getElementById('cardToken').value = data.token
                            window.Livewire.dispatch('update-card-token', { cardToken: data.token })
                        }
                    },
                    onError: function(errors) {
                        console.log('Erro do MP:', errors)

                        const fieldMap = {
                            cardNumber: 'cardNumber',
                            cardholderName: 'cardholderName',
                            securityCode: 'cardCVV',
                            expirationMonth: 'cardExpiration',
                            expirationYear: 'cardExpiration',
                            email: 'email',
                        }

                        const camposComErro = new Set()

                        errors.forEach(err => {
                            let field = null

                            if (err.field && fieldMap[err.field]) {
                                field = fieldMap[err.field]
                            } else {
                                if (err.message.includes('cardNumber')) field = 'cardNumber'
                                if (err.message.includes('expirationMonth') || err.message.includes('expirationYear')) field = 'cardExpiration'
                                if (err.message.includes('securityCode')) field = 'cardCVV'
                                if (err.message.includes('cardholderName')) field = 'cardholderName'
                                if (err.message.includes('email')) field = 'email'
                            }

                            if (field && !camposComErro.has(field)) {
                                camposComErro.add(field) // marca que já tratamos esse campo

                                const input = document.getElementById(field)
                                if (input) {
                                    // remove erro antigo se houver
                                    let errorEl = input.closest('.filament-forms-field-wrapper')?.querySelector('.mp-error')
                                    if (errorEl) errorEl.remove()

                                    // cria novo span
                                    errorEl = document.createElement('span')
                                    errorEl.classList.add('mp-error')
                                    errorEl.style.color = 'red'
                                    errorEl.style.fontSize = '12px'
                                    errorEl.style.display = 'block'
                                    errorEl.style.marginTop = '4px'
                                    errorEl.textContent = traduzMensagem(err.message, field)

                                    input.insertAdjacentElement('afterend', errorEl)
                                }
                            }
                        })

                        function traduzMensagem(msg, field) {
                            const traducoes = {
                                'cardNumber': 'Número do cartão inválido.',
                                'expirationMonth': 'Mês de validade inválido.',
                                'expirationYear': 'Ano de validade inválido.',
                                'cardExpiration': 'Data de validade inválida.',
                                'securityCode': 'Código de segurança inválido.',
                                'cardholderName': 'Nome do titular inválido.',
                                'identificationNumber': 'Documento inválido.',
                            }

                            return traducoes[field] || 'Erro ao validar os dados.'
                        }

                    },

                },
            })

        }

        let cardForm = null

        function metodoGo(tipo) {

            btn.type = 'button'
            btn.textContent = 'Confirmar Pagamento'
            btn.disabled = true
            btn.classList.add('opacity-50', 'cursor-not-allowed')
            btn.classList.add('disabled')

            const valueSelectMethodCheck = parseInt(event.target.value)
            const creditCardEnum = parseInt("{{ \Shieldforce\CheckoutPayment\Enums\MethodPaymentEnum::credit_card->value }}")

            if (valueSelectMethodCheck === creditCardEnum) {

                if (cardForm) {
                    cardForm.unmount?.()
                    cardForm = null
                }

                setTimeout(function() {
                    cardForm = initCardForm()

                    btn.textContent = 'Confirmar Pagamento'
                    btn.disabled = false
                    btn.classList.remove('opacity-50', 'cursor-not-allowed')
                    btn.classList.remove('disabled')

                    function bloquearAvanco(event) {
                        event.preventDefault()
                        event.stopImmediatePropagation()
                        document.getElementById('form-checkout-wizard').requestSubmit()
                    }

                    btn.addEventListener('click', bloquearAvanco)
                }, 1000)

            } else {

                if (cardForm) {
                    cardForm.unmount?.()
                    cardForm = null
                }
            }

        }
    </script>
@endpush
