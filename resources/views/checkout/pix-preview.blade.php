<div
    x-data="{
        base_qrcode: @entangle('base_qrcode').live,
        url_qrcode: @entangle('url_qrcode').live,
        first_name: @entangle('first_name').live,
        last_name: @entangle('last_name').live
    }"
    class="relative w-full h-[300px] rounded-xl shadow-lg p-6 select-none flex flex-col justify-center items-center
           bg-white dark:bg-gray-800 text-gray-900 dark:text-white transition-colors"
>
    <!-- Header -->
    <div class="flex justify-between items-center w-full max-w-[600px] mb-4">
        <span class="text-sm font-semibold uppercase tracking-wide">PIX</span>
        <span class="text-xs font-bold">QR Code</span>
    </div>

    <!-- QR Code centralizado -->
    <div class="flex items-center justify-center w-full max-w-[250px] h-[250px]">
        <template x-if="base_qrcode">
            <img
                :src="base_qrcode"
                alt="QR Code PIX"
                class="w-full h-full object-contain rounded-lg border border-gray-300 dark:border-white/30 shadow-md"
            />
        </template>
        <template x-if="!base_qrcode">
            <div
                class="w-full h-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center rounded-lg border border-gray-300 dark:border-white/30 shadow-md"
            >
                <img
                    src="https://img.icons8.com/fluent/512/pix.png"
                    alt="PIX Logo"
                    class="w-[120px] h-[120px] object-contain"
                />
            </div>
        </template>
    </div>

    <!-- Rodapé -->
    <div class="w-full max-w-[600px] flex justify-between items-center text-xs mt-4">
        <div class="truncate max-w-[60%]">
            <div class="opacity-80 text-[10px]">Nome do Pagador</div>
            <div class="font-semibold truncate" x-text="(first_name || 'Primeiro Nome') + ' ' + (last_name || 'Sobrenome')"></div>
        </div>
        <div class="flex flex-col items-end gap-1">
            <a
                :href="url_qrcode"
                target="_blank"
                class="text-xs font-bold underline hover:text-gray-900 dark:hover:text-white/90 transition-colors"
            >
                Abrir PIX
            </a>
            <a
                :href="url_qrcode"
                target="_blank"
                class="text-xs font-bold underline hover:text-gray-900 dark:hover:text-white/90 transition-colors"
            >
                Copia e Cola
            </a>
        </div>
    </div>
</div>
