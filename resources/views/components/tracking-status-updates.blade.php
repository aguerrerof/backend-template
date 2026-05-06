@php use function App\Helpers\splitWords; @endphp
@props(['fulfillment'])
<div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 bg-white dark:bg-gray-800 shadow-sm mb-4">
    <span class="text-gray-900 dark:text-gray-100">Novedades (proporcionado por {{$fulfillment->logisticProvider->name}})</span>
    @if(isset($fulfillment->tracking_info['tracking']))
        <div class="mb-4">
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 bg-white dark:bg-gray-900 shadow-sm mb-1">
                @foreach($fulfillment->tracking_info['tracking'] as $key => $element)
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                        @if(is_array($element) || is_object($element))
                            <label
                                class="block text-gray-700 dark:text-gray-200 font-medium text-xs">{{$element['nombre'] ?? null }}</label>
                            <input type="text" value="{{ $element['fecha'] ?? null }}"
                                   class="w-full bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs"
                                   readonly>
                        @else
                            <label class="block text-gray-700 dark:text-gray-200 font-medium text-xs">{{$key ?? null }}</label>
                            <input type="text" value="{{ $element }}"
                                   class="w-full bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs"
                                   readonly>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div x-show="open" x-transition
         class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4 max-h-[600px] overflow-y-auto">
        @foreach($fulfillment->tracking_info['novedades'] as $groupKey => $group)
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 bg-gray-50 dark:bg-gray-900 shadow-sm">
                @if(is_array($group) || is_object($group))
                    @foreach($group as $key => $value)
                        <div class="mb-1">
                            <label class="block text-gray-700 dark:text-gray-200 font-medium text-xs">{{ splitWords($key) }}</label>
                            <input type="text" value="{{ $value }}"
                                   class="w-full bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs"
                                   readonly>
                        </div>
                    @endforeach
                @else
                    <label class="block text-gray-700 dark:text-gray-200 font-medium text-xs">{{ $groupKey }}</label>
                    <input type="text" value="{{ $group }}"
                           class="w-full bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs"
                           readonly>
                @endif
            </div>
        @endforeach
    </div>
</div>
