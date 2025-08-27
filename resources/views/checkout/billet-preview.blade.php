<div class="flex flex-col items-center justify-center">
    @if($this->url_billet ?? $this->step4->url_billet ?? null)
        <iframe
            src="{{ $this->url_billet ?? $this->step4->url_billet ?? null }}"
            class="w-full min-h-[600px] rounded-lg border border-gray-300 dark:border-white/30 shadow-md">
        </iframe>


        <a
            href="{{ $this->url_billet ?? $this->step4->url_billet ?? null }}"
            target="_blank"
            class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
            Abrir boleto em nova aba
        </a>
    @else
        <p class="text-gray-500">Nenhum boleto gerado ainda.</p>
    @endif
</div>
