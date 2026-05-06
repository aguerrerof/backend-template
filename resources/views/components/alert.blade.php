@props([
    'type' => 'info', // info|success|warning|error
    'title' => null,
    'dismissible' => true,
])

@php
    $types = [
        'success' => [
            'wrap' => 'bg-green-50 text-green-800 ring-green-200 dark:bg-green-900/20 dark:text-green-200 dark:ring-green-800',
            'icon' => 'heroicon-s-check-circle',
        ],
        'warning' => [
            'wrap' => 'bg-amber-50 text-amber-900 ring-amber-200 dark:bg-amber-900/20 dark:text-amber-100 dark:ring-amber-800',
            'icon' => 'heroicon-s-exclamation-triangle',
        ],
        'error' => [
            'wrap' => 'bg-red-50 text-red-800 ring-red-200 dark:bg-red-900/20 dark:text-red-200 dark:ring-red-800',
            'icon' => 'heroicon-s-x-circle',
        ],
        'info' => [
            'wrap' => 'bg-blue-50 text-blue-800 ring-blue-200 dark:bg-blue-900/20 dark:text-blue-200 dark:ring-blue-800',
            'icon' => 'heroicon-s-information-circle',
        ],
    ];

    $variant = $types[$type] ?? $types['info'];
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-cloak
    {{ $attributes->merge(['class' => "rounded-xl ring-1 px-4 py-3 {$variant['wrap']}"]) }}
    role="alert"
>
    <div class="flex gap-3">
        <x-dynamic-component :component="$variant['icon']" class="h-5 w-5 flex-none mt-0.5"/>

        <div class="min-w-0 flex-1">
            @if($title)
                <div class="text-sm font-semibold">{{ $title }}</div>
            @endif
            <div class="text-sm leading-relaxed">
                {{ $slot }}
            </div>
        </div>

        @if($dismissible)
            <button
                type="button"
                class="flex-none rounded-md p-1.5 hover:bg-black/5 dark:hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 focus:ring-offset-transparent"
                @click="show = false"
                aria-label="Dismiss"
            >
                <x-heroicon-s-x-mark class="h-4 w-4"/>
            </button>
        @endif
    </div>
</div>
