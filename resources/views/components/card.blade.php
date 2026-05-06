@props([
    'padding' => 'p-4 sm:p-6',
])

<div {{ $attributes->merge(['class' => "rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 {$padding}"]) }}>
    {{ $slot }}
</div>
