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
    class="relative w-80 h-48 rounded-xl shadow-lg
           bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500
           text-black dark:text-white
           p-5 select-none rounded-xl"
            style="padding: 30px;border: 1px dashed #cecece;height: 300px"
>
    <!-- Header -->
    <div class="flex justify-between items-center">
        <span class="text-sm font-semibold" id="issuer-name-card">Meu Cartão</span>
        <span class="text-xs font-bold">

            <img
                id="img-brand-card"
                width="25"
                height="15"
                src="https://storage.googleapis.com/star-lab/blog/OGs/image-not-found.png"
                title=""
                alt=""
            />

        </span>
    </div>

    <!-- Número do cartão -->
    <div class="mt-8 text-xl tracking-widest font-mono">
        <span
            x-text="formatNumber(card_number)"
        >
        </span>
    </div>

    <br>
    <hr>
    <!-- Rodapé -->
    <div class="flex justify-between items-center mt-6">
        <div>
            <div class="text-xs opacity-80">Nome</div>
            <div
                class="uppercase tracking-wide text-sm font-semibold"
                 x-text="card_payer_name || 'NOME DO TITULAR'"
            >
            </div>
        </div>
        <div>
            <div class="text-xs opacity-80">Validade</div>
            <div
                class="tracking-wide font-semibold"
                x-text="card_validate || 'MM/AA'"
            >
            </div>
        </div>
    </div>
</div>
