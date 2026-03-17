<div class="space-y-4">
    @foreach($pagamentos as $pagamento)
        <div class="p-4 border rounded-lg">
            <p><strong>ID:</strong> {{ $pagamento['id'] }}</p>
            <p>
                <strong>Status:</strong>
                <span class="
                    @if($pagamento['status'] === 'approved') text-green-600
                    @elseif($pagamento['status'] === 'pending') text-yellow-600
                    @else text-red-600
                    @endif
                ">
                {{ $pagamento['status'] }}
                </span>
            </p>
            <p><strong>Método:</strong> {{ $pagamento['method'] }}</p>

            <details class="mt-2">
                <summary class="cursor-pointer text-sm text-gray-600">
                    Ver JSON completo
                </summary>

                <pre
                    class="
                        text-xs
                        bg-gray-100
                        p-2
                        rounded
                        mt-2
                        overflow-y-auto
                        max-h-60
                        whitespace-pre-wrap
                        break-words
                    "
                    style="overflow-y: scroll;overflow-x: scroll;height: 500px;"
                >{{ json_encode($pagamento['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </details>
        </div>
    @endforeach
</div>
