<div class="space-y-4">
    <p style="text-align: center;color: red;font-size: 14pt;">
        {{ $message ?? 'Nenhum resultado!' }}
    </p>

    @if(!empty($erro['message'] ?? null))
        <div class="p-4 border border-red-300 rounded-lg bg-red-50 dark:bg-red-950/30 text-sm">
            <p><strong>Erro na última tentativa de geração:</strong></p>
            <p class="mt-1">{{ $erro['message'] }}</p>

            @if(!empty($erro['code']))
                <p class="mt-1 text-xs text-gray-500">Código: {{ $erro['code'] }}</p>
            @endif
        </div>

        @if(!empty($recordId))
            <div style="text-align:center;">
                <button
                    type="button"
                    wire:click="tentarNovamente('{{ $recordId }}')"
                    wire:confirm="Se já existir uma cobrança ativa pra esse checkout, ela será cancelada e substituída por uma nova. Confirma?"
                    wire:loading.attr="disabled"
                    style="background:#16a34a;color:white;border-radius:5px;padding:8px 16px;"
                >
                    Tentar Gerar Novamente
                </button>
            </div>
        @endif
    @endif
</div>
