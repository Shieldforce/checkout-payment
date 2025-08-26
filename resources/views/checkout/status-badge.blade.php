<div>
    <span id="statusLabel" class="px-3 py-1 rounded bg-gray-200">
        {{ $this->statusCheckout }}
    </span>

    @if($this->checkout->startOnStep == 5)
        {{-- Atualiza sozinho a cada 30s --}}
        <div wire:poll.30s="refreshStatusCheckout"></div>
    @endif
</div>
