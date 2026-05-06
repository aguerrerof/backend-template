@props(['fulfillments', 'order'])
@php use App\Models\Enums\FulfillmentStatus; @endphp

<div class="bg-white dark:bg-gray-800 overflow-auto shadow-sm sm:rounded-lg p-4 mb-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4">
        <div>
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Despachos (Fulfillment)</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ method_exists($fulfillments, 'total') ? $fulfillments->total() : count($fulfillments) }}
                registro(s)
            </div>
        </div>

        <a href="{{ route('orders.create-fulfillment', array_merge(['orderId' => $order->id], request()->query())) }}"
           class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
            <x-heroicon-s-plus class="w-4 h-4 mr-2"/>
            Registrar nuevo
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-auto">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Proveedor Logístico
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"># de Guía</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estado</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Encargado</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acciones</th>
                </tr>
                </thead>

                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($fulfillments as $fulfillment)
                    <tr
                        @class([
                            'hover:bg-gray-50 dark:hover:bg-gray-700/40',
                            'bg-yellow-100' => $fulfillment->status === FulfillmentStatus::PENDING->value,
                            'bg-blue-100' => $fulfillment->status === FulfillmentStatus::PICKED_UP->value,
                            'bg-sky-100' => $fulfillment->status === FulfillmentStatus::IN_WAREHOUSE->value,
                            'bg-green-100' => $fulfillment->status === FulfillmentStatus::IN_TRANSIT->value,
                            'bg-teal-100' => $fulfillment->status === FulfillmentStatus::OUT_FOR_DELIVERY->value,
                            'bg-orange-100' => $fulfillment->status === FulfillmentStatus::RETURNING->value,
                            'bg-lime-100' => $fulfillment->status === FulfillmentStatus::DELIVERED->value,
                            'bg-purple-100' => $fulfillment->status === FulfillmentStatus::RETURNED->value,
                            'bg-pink-100' => $fulfillment->status === FulfillmentStatus::ISSUE->value,
                            'bg-red-100' => $fulfillment->status === FulfillmentStatus::CANCELLED->value,
                            'bg-gray-100' => $fulfillment->status === FulfillmentStatus::BOOKED->value,
                            'bg-indigo-100' => $fulfillment->status === FulfillmentStatus::DISPATCHED->value,
                        ])>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                            {{ $fulfillment->logisticProvider->name ?? '—' }}
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                            {{ $fulfillment->tracking_number ?? '—' }}
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                            {{ __('fulfillment-status.'.$fulfillment->status) ?? '—' }}
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                            {{ $fulfillment->user->name ?? '—' }}
                        </td>
                        <td class="px-3 py-2 text-sm text-center">
                            <a href="{{ route('fulfillments.show', array_merge(['id' => $fulfillment->id], request()->query())) }}"
                               class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700 transition">
                                <x-heroicon-s-magnifying-glass class="w-4 h-4 mr-2"/>
                                Ver
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                            No se encontraron registros.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(method_exists($fulfillments, 'links'))
        <div class="mt-3 flex justify-end">
            {{ $fulfillments->links('pagination::tailwind') }}
        </div>
    @endif
</div>
