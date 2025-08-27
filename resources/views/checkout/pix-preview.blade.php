<div
    x-data="{
        first_name: @entangle('first_name').live,
        last_name: @entangle('last_name').live
    }"
    class="relative w-full max-w-md mx-auto h-[300px] rounded-xl shadow-lg p-4 sm:p-6 select-none flex flex-col justify-between
           bg-white dark:bg-gray-800 text-gray-900 dark:text-white transition-colors"
>
    <!-- Header -->
    <div class="flex justify-between items-center w-full mb-2">
        <span class="text-sm font-semibold uppercase tracking-wide">PIX</span>
        <span class="text-xs font-bold">QR Code</span>
    </div>

    <!-- QR Code centralizado -->
    <div class="flex-1 flex items-center justify-center w-full">
        @if($this->base_qrcode ?? $this->step4->base_qrcode ?? null)

            <img
                src="data:image/png;base64,{{ $this->base_qrcode ?? $this->step4->base_qrcode ?? null }}"
                alt="QR Code PIX"
                width="150"
                height="150"
                class="
                    w-full
                    max-w-[150px]
                    h-auto
                    object-contain
                    rounded-lg
                    border
                    border-gray-300
                    dark:border-white/30
                    shadow-md
                "
            />

        @else

            <div
                class="
                    w-full
                    max-w-[150px]
                    h-[150px]
                    bg-gray-200
                    dark:bg-gray-700
                    flex
                    items-center
                    justify-center
                    rounded-lg
                    border
                    border-gray-300
                    dark:border-white/30
                    shadow-md
                "
            >
                <img
                    src="https://img.pikbest.com/png-images/20190918/cartoon-snail-loading-loading-gif-animation_2734139.png!f305cw"
                    alt="PIX Logo"
                    width="150"
                    height="150"
                    class="w-[120px] h-[120px] object-contain"
                />
                Aguardando para gerar pix ...
            </div>

        @endif
    </div>

    <!-- Rodapé -->
    <div class="w-full flex flex-col sm:flex-row justify-between items-start sm:items-center text-xs mt-2 gap-2 sm:gap-0">
        <div class="truncate max-w-full sm:max-w-[60%]">
            <div class="opacity-80 text-[10px]">Nome do Pagador</div>
            <div class="font-semibold truncate" x-text="(first_name || 'Primeiro Nome') + ' ' + (last_name || 'Sobrenome')"></div>
        </div>
        <div class="flex flex-col sm:items-end gap-1">
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

