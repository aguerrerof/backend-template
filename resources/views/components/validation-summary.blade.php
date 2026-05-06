@props([
    'title' => 'Errores al procesar su solicitud',
])

@if ($errors->any())
    <x-alert type="error" :title="$title">
        <ul class="mt-2 list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif

