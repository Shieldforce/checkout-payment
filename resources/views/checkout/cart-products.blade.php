<div class="space-y-4">
    @foreach ($this->items as $item)
        <div class="flex items-center space-x-4 border p-3 rounded-lg shadow-sm bg-white">
            <img
                src="{{ $item["img"] ??
                        "https://static.vecteezy.com/system/resources/previews/028/047/017/non_2x/3d-check-product-free-png.png" }}"
                alt="{{ $item["name"] }}"
                class="w-16 h-16 object-cover rounded-md"
                style="margin-right: 20px;border-right: 1px dashed #cecece"
            >

            <div class="flex flex-col">
                <span class="font-semibold text-gray-800">{{ $item["name"] }}</span>
                <span class="text-sm text-gray-600">{{ $item["description"] ?? 'Sem descrição' }}</span>
                <span class="text-sm text-green-600 font-bold">
                    R$ {{ number_format($item["price"], 2, ',', '.') }}
                </span>
                <span class="text-xs text-gray-500">Qtd: {{ $item["quantity"] }}</span>
            </div>
        </div>
    @endforeach
</div>
