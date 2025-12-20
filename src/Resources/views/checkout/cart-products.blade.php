{{--
<div class="space-y-4">
    @if(isset($this->items))
        @foreach ($this->items as $item)
            <div class="flex items-center space-x-4 border p-3 rounded-lg shadow-sm bg-white">
                <img
                    src="{{ $item["img"] ??
                        "https://static.vecteezy.com/system/resources/previews/028/047/017/non_2x/3d-check-product-free-png.png" }}"
                    alt="{{ $item["name"] }}"
                    class="w-16 h-16 object-cover rounded-md"
                    style="margin-right: 10px;border-right: 1px dashed #cecece;padding-right: 10px;"
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
    @endif
</div>
--}}

<div class="space-y-4">
    @if(isset($this->items))
        @foreach ($this->items as $item)
            <div
                class="flex items-center space-x-4 border p-3 rounded-lg shadow-sm
                       bg-white dark:bg-gray-800
                       border-gray-200 dark:border-gray-700"
            >
                <img
                    src="{{ $item["img"] ??
                        "https://static.vecteezy.com/system/resources/previews/028/047/017/non_2x/3d-check-product-free-png.png" }}"
                    alt="{{ $item["name"] }}"
                    class="
                        w-16 h-16
                        object-cover
                        rounded-md
                        pr-2
                        mr-4
                        border-r
                        border-dashed
                        border-gray-300
                        dark:border-gray-600
                    "
                >

                <div class="flex flex-col ml-4" style="margin-left: 30px !important;">
                    <span class="font-semibold text-gray-800 dark:text-gray-100">
                        {{ $item["name"] }}
                    </span>
                    <span class="text-sm text-gray-600 dark:text-gray-300">
                        {!! $item["description"] ?? 'Sem descrição' !!}
                    </span>
                    <span class="text-sm text-green-600 dark:text-green-400 font-bold">
                        R$ {{ number_format($item["price"], 2, ',', '.') }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Qtd: {{ $item["quantity"] }}
                    </span>
                </div>
            </div>
        @endforeach
    @endif
</div>
