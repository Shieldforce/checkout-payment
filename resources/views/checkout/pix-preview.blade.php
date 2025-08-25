<div
    x-data="{
        pix_qrcode: @entangle('base_qrcode').live,
        pix_url: @entangle('url_qrcode').live,
        payer_name: @entangle('card_payer_name').live
    }"
    class="relative w-80 h-48 rounded-xl shadow-lg
           bg-gradient-to-r from-green-400 via-green-500 to-green-600
           text-black dark:text-white
           p-5 select-none flex flex-col justify-between items-center"
>
    <!-- Header -->
    <div class="flex justify-between items-center w-full">
        <span class="text-sm font-semibold">PIX</span>
        <span class="text-xs font-bold">QR Code</span>
    </div>

    <!-- QR Code -->
    <div class="flex-1 flex items-center justify-center mt-2 mb-2">
        <template x-if="pix_qrcode">
            <img :src="pix_qrcode" alt="QR Code PIX" class="w-24 h-24 object-contain rounded-md border border-white/30 shadow" />
        </template>
        <template x-if="!pix_qrcode">
            <div class="w-24 h-24 bg-white/20 flex items-center justify-center text-xs text-white/70 rounded-md">
                QR Code
            </div>
        </template>
    </div>

    <!-- Rodapé -->
    <div class="w-full flex justify-between items-center text-xs">
        <div>
            <div class="opacity-80">Nome do Pagador</div>
            <div class="font-semibold truncate max-w-[100px]" x-text="payer_name || 'NOME DO PAGADOR'"></div>
        </div>
        <div>
            <a :href="pix_url" target="_blank" class="text-xs font-bold underline">
                Abrir PIX
            </a>
        </div>
    </div>
</div>
