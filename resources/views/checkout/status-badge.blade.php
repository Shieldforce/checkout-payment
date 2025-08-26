<span class="px-2 py-1 rounded-full text-white {{ $checkout->status === 2 ? 'bg-green-600' : 'bg-yellow-600' }}">
    {{ $checkout->status === 2 ? 'Aprovado' : 'Em Processamento' }}
</span>
