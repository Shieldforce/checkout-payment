<x-filament::page>
    {{ $this->form }}

    @if($this->cppGateways)
        <x-filament::button wire:click="submit" class="mt-4">
            Finalizar Pagamento
        </x-filament::button>
    @endif
</x-filament::page>
