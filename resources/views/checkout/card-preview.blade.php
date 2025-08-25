<div
    x-data="{
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
    }"
    class="relative w-80 h-48 rounded-xl shadow-lg bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white p-5 select-none"
>
    <!-- Header -->
    <div class="flex justify-between items-center">
        <span class="text-sm">Meu Cartão</span>
        <span class="text-xs">VISA</span>
    </div>

    <!-- Número do cartão -->
    <div class="mt-8 text-xl tracking-widest font-mono">
        <span x-text="formatNumber(card_number)"></span>
    </div>

    <!-- Rodapé -->
    <div class="flex justify-between items-center mt-6">
        <div>
            <div class="text-xs">Nome</div>
            <div class="uppercase tracking-wide text-sm" x-text="card_payer_name || 'NOME DO TITULAR'"></div>
        </div>
        <div>
            <div class="text-xs">Validade</div>
            <div class="tracking-wide" x-text="card_validate || 'MM/AA'"></div>
        </div>
    </div>
</div>
