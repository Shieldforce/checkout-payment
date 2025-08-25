<x-filament::page>
    {{ $this->form }}

    {{--@if($this->cppGateways)
        <x-filament::button wire:click="submit" class="mt-4">
            Finalizar Pagamento
        </x-filament::button>
    @endif--}}
</x-filament::page>

@if($this->cppGateways->field_2)
    @push('scripts')


        <script src="https://sdk.mercadopago.com/js/v2"></script>
        <script>
            document.addEventListener('DOMContentLoaded', async () => {

                const mp = new MercadoPago("{{ $cppGateways->field_2 ?? null  }}")

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

            });
        </script>
    @endpush
@endif
