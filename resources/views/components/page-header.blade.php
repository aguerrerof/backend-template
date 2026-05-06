@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between']) }}>
    <div class="min-w-0">
        <h1 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $title }}</h1>
        @if($subtitle)
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2 sm:justify-end">
            {{ $actions }}
        </div>
    @endisset
</div>
