<div
    class="
        relative
        w-80
        h-48
        rounded-xl
        shadow-lg
        bg-gradient-to-r
        from-indigo-500
        via-purple-500
        to-pink-500
        text-white p-5
    "
>
    <div class="flex justify-between items-center">
        <span class="text-sm">Meu Cartão</span>
        <span class="text-xs">VISA</span>
    </div>
    <div class="mt-8 text-xl tracking-widest">
        {{ $this->card_number ?: '0000 0000 0000 0000' }}
    </div>
    <div class="flex justify-between items-center mt-6">
        <div>
            <div class="text-xs">Nome</div>
            <div>{{ $this->card_payer_name ?: 'NOME DO TITULAR' }}</div>
        </div>
        <div>
            <div class="text-xs">Validade</div>
            <div>{{ $this->card_validate ?: 'MM/AA' }}</div>
        </div>
    </div>
</div>
