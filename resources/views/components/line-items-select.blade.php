@props(['lineItems', 'productImages', 'usedItemIds' => [], 'title'])

<div class="bg-white dark:bg-gray-800 overflow-auto shadow-sm sm:rounded-lg p-4">
    @if($lineItems)
        <div class="flex items-center justify-between mb-4">
            <div>
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Confirma el peso por ítem (gramos).</div>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ is_countable($lineItems) ? count($lineItems) : 0 }} item(s)
            </div>
        </div>

        <div x-data="{ openImage: false, imgSrc: '' }" class="relative">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($lineItems as $lineItem)
                    @php
                        $image = $productImages[$lineItem['product_id']] ?? 'https://via.placeholder.com/150';
                        $isDisabled = in_array($lineItem['id'], $usedItemIds);
                    @endphp

                    <div
                        @class([
                            'border rounded-lg bg-white dark:bg-gray-900 overflow-hidden dark:border-gray-700',
                            'opacity-60' => $isDisabled,
                        ])
                    >
                        <div class="flex gap-3 p-3">
                            <button type="button"
                                    class="shrink-0"
                                    @click.prevent="imgSrc='{{ $image }}'; openImage=true"
                                    title="Ver imagen">
                                <img src="{{ $image }}"
                                     alt="{{ $lineItem['title'] }}"
                                     class="w-20 h-20 object-cover rounded-md border border-gray-100 dark:border-gray-800">
                            </button>

                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $lineItem['title'] }}</div>
                                @if(!empty($lineItem['variant_title']))
                                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $lineItem['variant_title'] }}</div>
                                @endif
                                @if(!empty($lineItem['sku']))
                                    <div class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">SKU: {{ $lineItem['sku'] }}</div>
                                @endif
                                <div class="text-sm font-semibold text-gray-800 dark:text-gray-100 mt-1">${{ $lineItem['price'] }}</div>
                            </div>
                        </div>

                        @if($isDisabled)
                            <div class="px-3 pb-3">
                                <div class="text-xs font-semibold text-red-600">Ya fue incluido en otro despacho.</div>
                            </div>
                        @endif

                        <div class="px-3 pb-3">
                            <label class="block text-gray-700 dark:text-gray-200 font-medium text-sm mb-1">Peso (gramos)</label>
                            <input type="number"
                                   min="1"
                                   name="items_weight[]"
                                   required
                                   value="{{ $lineItem['grams'] ?? null }}"
                                   class="w-full bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>

                        <input type="hidden" name="line_items[]" value='@json($lineItem)'>
                    </div>
                @endforeach
            </div>

            <div x-show="openImage" x-transition.opacity
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm"
                 @click="openImage=false" style="display:none">
                <div class="relative" @click.stop>
                    <img :src="imgSrc" class="max-h-[85vh] max-w-[85vw] rounded-xl shadow-2xl object-contain" alt="">
                    <button type="button"
                            @click="openImage=false"
                            class="absolute -top-10 right-0 bg-white/90 text-gray-900 rounded-full px-3 py-2 hover:bg-white shadow-md transition"
                            title="Cerrar">
                        ×
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
