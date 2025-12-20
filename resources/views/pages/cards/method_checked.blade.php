@php
    $opcoes = [
    [
        'id' => 1,
        'titulo' => 'Cartão',
        'icone' => 'heroicon-o-credit-card',
        'descricao' => 'Pagamento no Cartão de Crédito',
    ],
    [
        'id' => 3,
        'titulo' => 'Pix',
        'icone' => 'heroicon-o-qr-code',
        'descricao' => 'Pagamento no Pix',
    ],
    [
        'id' => 4,
        'titulo' => 'Boleto',
        'icone' => 'heroicon-o-document-text',
        'descricao' => 'Pagamento no Boleto',
    ],
];

    $selecionado = $getState();
    $tipoUrl = request()->query('tipo');
@endphp

<div
    x-data="{
        selecionado: Number(@entangle($getStatePath())),
        init() {
            // se a URL tiver o parâmetro tipo, já seleciona automaticamente
            const tipoUrl = Number(new URL(window.location.href).searchParams.get('tipo'));
            if (tipoUrl && !this.selecionado) {
                this.selecionado = tipoUrl;
            }
        }
    }"
    x-init="init()"
    class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4"
>
    @foreach ($opcoes as $opcao)
        <div
            wire:click="$set('method_checked', '{{ $opcao['id'] }}')"
            @click="
                selecionado = {{ $opcao['id'] }};
            "
            :class="selecionado == @js($opcao['id'])
                ? 'bg-primary-500 text-white dark:bg-primary-600 dark:text-white shadow-lg ring-2 ring-primary-400 border-transparent'
                : 'bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 hover:border-primary-400 hover:bg-primary-50 dark:hover:bg-gray-700'"
            class="cursor-pointer rounded-2xl border-2 p-6 transition-all duration-200 cursor-pointer"
        >
            <div class="flex flex-col items-center justify-center text-center space-y-2">
                <x-dynamic-component :component="$opcao['icone']" class="w-10 h-10 text-primary-600"/>
                <div class="font-semibold text-lg">{{ $opcao['titulo'] }}</div>
                <p class="text-sm">{{ $opcao['descricao'] }}</p>
            </div>
        </div>
    @endforeach
</div>

@if (!$selecionado)
    <p class="text-center text-gray-400 mt-2 text-sm italic">
        Clique em uma das opções acima para fazer o pagamento.
    </p>
@endif
