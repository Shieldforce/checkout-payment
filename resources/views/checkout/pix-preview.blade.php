<div
    x-data="{
        base_qrcode: @entangle('base_qrcode').live,
        url_qrcode: @entangle('url_qrcode').live,
        first_name: @entangle('first_name').live,
        last_name: @entangle('last_name').live,
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
        <template x-if="base_qrcode">
            @if($this->qrcode_yes)
                <img
                    :src="base_qrcode" alt="QR Code PIX"
                    class="w-24 h-24 object-contain rounded-md border border-white/30 shadow"
                />
            @else
                <img
                    src="https://img.icons8.com/fluent/512/pix.png" alt="QR Code PIX"
                    class="w-24 h-24 object-contain rounded-md border border-white/30 shadow"
                />
            @endif
        </template>
        <template x-if="!base_qrcode">
            <div class="w-24 h-24 bg-white/20 flex items-center justify-center text-xs text-white/70 rounded-md">
                QR Code
            </div>
        </template>
    </div>

    <!-- Rodapé -->
    <div class="w-full flex justify-between items-center text-xs">
        <div>
            <div class="opacity-80">Nome do Pagador</div>
            <div
                class="font-semibold truncate max-w-[100px]"
                x-text="first_name || 'Primeiro Nome'"
                x-text="last_name || 'Sobrenome'"
            ></div>
        </div>
        <div>
            <a :href="url_qrcode" target="_blank" class="text-xs font-bold underline">
                Abrir PIX
            </a>
        </div>
    </div>
</div>
