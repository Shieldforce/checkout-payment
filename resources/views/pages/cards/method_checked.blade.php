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
@endphp

<div
    x-data="{
        selecionado: @entangle($getStatePath()).defer,
        init() {
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
            @click="selecionado = {{ $opcao['id'] }}"
            :class="selecionado === {{ $opcao['id'] }}
                ? 'bg-primary-500 text-white dark:bg-primary-600 shadow-lg ring-2 ring-primary-400 border-transparent'
                : 'bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 hover:border-primary-400 hover:bg-primary-50 dark:hover:bg-gray-700'"
            class="cursor-pointer rounded-2xl p-6 transition-all duration-200"
        >
            <div class="flex flex-col items-center justify-center text-center space-y-2">
                <x-dynamic-component
                    :component="$opcao['icone']"
                    :class="selecionado === {{ $opcao['id'] }}
                        ? 'w-10 h-10 text-white'
                        : 'w-10 h-10 text-primary-600'"
                />

                <div class="font-semibold text-lg">
                    {{ $opcao['titulo'] }}
                </div>

                <p class="text-sm opacity-90">
                    {{ $opcao['descricao'] }}
                </p>
            </div>
        </div>
    @endforeach
</div>

@if (!$getState())
    <p class="text-center text-gray-400 mt-2 text-sm italic">
        Clique em uma das opções acima para fazer o pagamento.
    </p>
@endif
