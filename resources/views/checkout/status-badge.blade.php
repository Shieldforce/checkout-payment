<div>
    <span id="statusLabel" class="px-3 py-1 rounded bg-gray-200">
        {{ $this->statusCheckout }}
    </span>

    {{-- Atualiza sozinho a cada 30s --}}
    <div wire:poll.30s="refreshStatusCheckout"></div>
</div>
