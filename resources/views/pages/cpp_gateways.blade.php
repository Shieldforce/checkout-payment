<x-filament::page>
    <div class="mb-4">
        {{ $this->table }}
    </div>

    <div class="mt-6">
        {{ $this->form }}
        <x-filament::button wire:click="save">
            {{ $record ? 'Atualizar' : 'Criar' }}
        </x-filament::button>
    </div>
</x-filament::page>
