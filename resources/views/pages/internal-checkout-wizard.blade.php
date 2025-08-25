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
        <script>
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
        </script>
    @endpush
@endif
