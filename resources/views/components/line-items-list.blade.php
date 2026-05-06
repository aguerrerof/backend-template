@props(['lineItems', 'productImages', 'title'])

<div class="bg-white dark:bg-gray-800 overflow-auto shadow-sm sm:rounded-lg p-4">
    @if($lineItems)
        <div class="mb-3 p-3 bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-200 rounded text-sm">
            {{ $title }}
        </div>
        <div x-data="{
            open: false,
            imgSrc: '',
            show(src) {
                this.imgSrc = src;
                this.open = true;
                document.body.classList.add('overflow-hidden');
            },
            close() {
                this.open = false;
                document.body.classList.remove('overflow-hidden');
            }
        }" class="relative">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($lineItems as $lineItem)
                    @php
                        $image = $productImages[$lineItem['product_id']] ?? 'https://via.placeholder.com/150';
                    @endphp
                    <div class="flex flex-col items-center border dark:border-gray-700 p-3 rounded-lg bg-white dark:bg-gray-900 cursor-pointer hover:scale-105 hover:shadow-lg transition"
                         @click="show('{{ $image }}')">
                        <img src="{{ $image }}" alt="{{ $lineItem['title'] }}"
                             class="w-48 h-48 object-cover rounded-lg mb-2">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200 text-center">{{ $lineItem['title'] }}</p>
                        @if(!empty($lineItem['variant_title']))
                            <p class="text-xs text-gray-500 dark:text-gray-400 text-center">{{ $lineItem['variant_title'] }}</p>
                        @endif
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center mt-1">${{ $lineItem['price'] }}</p>
                        @if(!empty($lineItem['sku']))
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">SKU: {{ $lineItem['sku'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>

            <div x-show="open" x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm"
                 @click="close()" @keydown.escape.window="close()" style="display: none;">
                <div class="relative" @click.stop>
                    <img :src="imgSrc" class="max-h-[85vh] max-w-[85vw] rounded-xl shadow-2xl object-contain">
                    <button @click="close()"
                            class="absolute -top-8 right-0 bg-white/80 dark:bg-gray-800/80 text-gray-900 dark:text-gray-100 rounded-full p-2 hover:bg-white dark:hover:bg-gray-800 shadow-md transition">
                        ✕
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
