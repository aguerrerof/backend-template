@props(['images','title'])

<div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
    <div class="mb-3 p-3 bg-blue-500 text-white rounded text-sm">
        {{ $title }}
    </div>

    <div
        x-data="{
            open: false,
            imgSrc: '',
            show(src) {
                if (!src.startsWith('data:image')) {
                    src = 'data:image/jpeg;base64,' + src;
                }
                this.imgSrc = src;
                this.open = true;
                document.body.classList.add('overflow-hidden');
            },
            close() {
                this.open = false;
                document.body.classList.remove('overflow-hidden');
            }
        }"
        class="relative"
    >
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($images as $image)
                @php
                    $src = $image['imagen'] ?? null;
                    if ($src && !str_starts_with($src, 'data:image')) {
                        $src = 'data:image/jpeg;base64,' . $src;
                    }
                @endphp

                @if(empty($src))
                    @continue
                @endif

                <div
                    class="flex flex-col items-center border dark:border-gray-700 p-3 rounded-lg bg-white dark:bg-gray-900 cursor-pointer hover:scale-105 hover:shadow-lg transition"
                    @click="show('{{ $src }}')"
                >
                    <img src="{{ $src }}" alt="{{ $image['tipo'] ?? 'Imagen' }}"
                         class="w-48 h-48 object-cover rounded-lg mb-2">

                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200 text-center">{{ $image['tipo'] ?? 'NA' }}</p>

                    @if(!empty($image['estado']))
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center mt-1">{{ $image['estado'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Modal -->
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm"
            @click="close()"
            @keydown.escape.window="close()"
            style="display: none;"
        >
            <div class="relative" @click.stop>
                <img :src="imgSrc" class="max-h-[85vh] max-w-[85vw] rounded-xl shadow-2xl object-contain">
                <button @click="close()"
                        class="absolute -top-8 right-0 bg-white/80 dark:bg-gray-800/80 text-gray-900 dark:text-gray-100 rounded-full p-2 hover:bg-white dark:hover:bg-gray-800 shadow-md transition">
                    ✕
                </button>
            </div>
        </div>
    </div>
</div>
