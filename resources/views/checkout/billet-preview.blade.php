<div class="flex flex-col items-center justify-center">
    @if($this->url_billet ?? $this->step4->url_billet ?? null)
        <iframe
            src="{{ $this->url_billet ?? $this->step4->url_billet ?? null }}"
            class="w-full min-h-screen rounded-lg border border-gray-300 dark:border-white/30 shadow-md">
        </iframe>
    @else
        <img
            src="https://img.pikbest.com/png-images/20190918/cartoon-snail-loading-loading-gif-animation_2734139.png!f305cw"
            alt="PIX Logo"
            width="150"
            height="150"
            class="w-[120px] h-[120px] object-contain"
        />
        <p class="text-gray-500">Aguardando para gerar o boleto ...</p>
    @endif
</div>
