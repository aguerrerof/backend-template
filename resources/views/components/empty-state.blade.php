@props([
    'title' => 'No hay resultados',
    'description' => null,
    'actionHref' => null,
    'actionLabel' => null,
    'icon' => 'heroicon-o-inbox',
])

<div {{ $attributes->merge(['class' => 'py-10 px-4 sm:px-6 text-center']) }}>
    <x-dynamic-component :component="$icon" class="mx-auto h-10 w-10 text-gray-400 dark:text-gray-500"/>
    <h3 class="mt-3 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h3>
    @if($description)
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $description }}</p>
    @endif
    @if($actionHref && $actionLabel)
        <div class="mt-5">
            <a href="{{ $actionHref }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition">
                {{ $actionLabel }}
            </a>
        </div>
    @endif
</div>
