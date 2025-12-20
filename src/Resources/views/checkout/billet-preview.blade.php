<div class="flex flex-col items-center justify-center">
    @php
        $billetUrl = $this->url_billet ?? $this->step4->url_billet ?? null;
    @endphp

    @if($billetUrl)
        <iframe
            src="{{ $this->url_billet ?? $this->step4->url_billet ?? null }}"
            class="w-full min-h-screen rounded-lg border border-gray-300 dark:border-white/30 shadow-md">
        </iframe>

        {{--<iframe
            src="https://docs.google.com/viewer?embedded=true&url={{ urlencode($billetUrl) }}"
            class="w-full min-h-screen rounded-lg border border-gray-300 dark:border-white/30 shadow-md"
        ></iframe>--}}

        <a href="{{ $billetUrl }}" target="_blank"
           class="mt-3 text-blue-600 dark:text-blue-400 underline">
            Baixar boleto em PDF
        </a>
    @else
        <img
            src="{{ asset("/images/loading_2.gif") }}w"
            alt="PIX Logo"
            width="150"
            height="150"
            class="w-[120px] h-[120px] object-contain"
        />
        <p class="text-gray-500">Aguardando para gerar o boleto ...</p>
    @endif
</div>
