<div class="space-y-3">
    @forelse($logs as $log)
        <div class="p-4 border rounded-lg">
            <p>
                <strong>{{ $log->created_at->format('d/m/Y H:i:s') }}</strong>
                &mdash;
                {{ $log->type === 'pix' ? 'Pix' : 'Boleto' }}

                <span class="
                    @if($log->action === 'generated') text-green-600
                    @elseif($log->action === 'regenerated') text-yellow-600
                    @else text-gray-500
                    @endif
                ">
                    ({{ match($log->action) {
                        'generated' => 'gerado',
                        'regenerated' => 'regerado, substituiu o anterior',
                        'reused' => 'reaproveitado, já existia igual',
                        default => $log->action,
                    } }})
                </span>
            </p>

            <p>
                <strong>Origem:</strong>
                {{ $log->origin === 'manual' ? 'Manual' : 'Automático' }}

                &mdash;

                <strong>Gerado por:</strong>
                {{ $log->user->name ?? 'Sistema' }}
            </p>

            <p>
                <strong>Valor:</strong>
                R$ {{ number_format($log->value ?? 0, 2, ',', '.') }}

                @if($log->due_date)
                    &mdash; <strong>Vencimento:</strong> {{ \Illuminate\Support\Carbon::parse($log->due_date)->format('d/m/Y') }}
                @endif
            </p>

            @if($log->mp_payment_id)
                <p class="text-sm text-gray-500">ID no Mercado Pago: {{ $log->mp_payment_id }}</p>
            @endif
        </div>
    @empty
        <p style="text-align: center;color: gray;">Nenhuma tentativa de cobrança registrada ainda.</p>
    @endforelse
</div>
