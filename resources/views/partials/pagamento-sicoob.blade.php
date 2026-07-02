<div class="space-y-4">
    @foreach($pagamentos as $pagamento)

        <div class="p-4 border rounded-lg">

            <p><strong>Nosso Número:</strong> {{ $pagamento['nossoNumero'] }}</p>

            <p><strong>Seu Número:</strong> {{ $pagamento['seuNumero'] }}</p>

            <p>
                <strong>Status:</strong>

                <span class="
                    @if($pagamento['situacaoBoleto'] === 'Em Aberto')
                        text-yellow-600
                    @elseif($pagamento['situacaoBoleto'] === 'Liquidado')
                        text-green-600
                    @elseif($pagamento['situacaoBoleto'] === 'Baixado')
                        text-red-600
                    @else
                        text-gray-600
                    @endif
                ">
                    {{ $pagamento['situacaoBoleto'] }}
                </span>
            </p>

            <p><strong>Valor:</strong> R$ {{ number_format($pagamento['valor'], 2, ',', '.') }}</p>

            <p><strong>Emissão:</strong> {{ \Carbon\Carbon::parse($pagamento['dataEmissao'])->format('d/m/Y') }}</p>

            <p><strong>Vencimento:</strong> {{ \Carbon\Carbon::parse($pagamento['dataVencimento'])->format('d/m/Y') }}</p>

            <p><strong>Linha Digitável:</strong></p>

            <div class="bg-gray-100 p-2 rounded break-all text-xs">
                {{ $pagamento['linhaDigitavel'] }}
            </div>

            @if(!empty($pagamento['codigoBarras']))
                <p class="mt-2"><strong>Código de Barras:</strong></p>

                <div class="bg-gray-100 p-2 rounded break-all text-xs">
                    {{ $pagamento['codigoBarras'] }}
                </div>
            @endif

            @if(!empty($pagamento['qrCode']))
                <p class="mt-2"><strong>PIX Copia e Cola:</strong></p>

                <textarea
                    readonly
                    class="w-full text-xs rounded border p-2"
                    rows="5"
                >{{ $pagamento['qrCode'] }}</textarea>
            @endif

            <details class="mt-4">
                <summary class="cursor-pointer text-sm text-gray-600">
                    Ver JSON completo
                </summary>

                <pre
                    class="text-xs bg-gray-100 p-2 rounded mt-2 overflow-auto max-h-96 whitespace-pre-wrap break-words"
                >{{ json_encode($pagamento, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </details>

            <hr class="my-4">

            @if($pagamento['situacaoBoleto'] === 'Em Aberto')

                <button
                    type="button"
                    wire:click="consultarBoleto('{{ $pagamento['nossoNumero'] }}', '{{ $record->id }}')"
                    style="background:orange;color:white;border-radius:5px;padding:5px;"
                >
                    Atualizar Status
                </button>

                <button
                    type="button"
                    wire:click="cancelarBoleto('{{ $pagamento['nossoNumero'] }}', '{{ $record->id }}')"
                    style="background:red;color:white;border-radius:5px;padding:5px;"
                >
                    Cancelar Boleto
                </button>

            @endif

        </div>

    @endforeach
</div>
