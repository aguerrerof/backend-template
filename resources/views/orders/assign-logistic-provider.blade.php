<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Asignar proveedor') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-auto shadow-sm sm:rounded-lg p-6">
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Form --}}
                <form action="{{ route('orders.assign-logistic-provider', $order->id) }}" method="POST"
                      class="space-y-6">
                    @csrf
                    <div
                        x-data="autocomplete({{ $order->logisticProvider ? $order->logisticProvider->id : 'null' }}, '{{ $order->logisticProvider ? $order->logisticProvider->name : '' }}')"
                        class="relative"
                    >
                        <label for="provider" class="block text-gray-700 font-medium mb-1">Proveedor</label>

                        <div class="relative">
                            <input
                                type="text"
                                id="provider"
                                required="true"
                                x-model="query"
                                @input.debounce.300ms="search()"
                                @focus="open = true"
                                @click.away="open = false"
                                placeholder="Seleccione un proveedor"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 pr-10"
                                autocomplete="off"
                            />

                            <!-- Clear button -->
                            <button
                                type="button"
                                x-show="selectedId"
                                @click="clear()"
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                title="Eliminar proveedor"
                            >
                                ✕
                            </button>
                        </div>

                        {{-- Dropdown --}}
                        <ul
                            x-show="open && filteredOptions.length > 0"
                            class="absolute z-10 w-full bg-white border rounded-lg mt-1 max-h-40 overflow-auto shadow-lg"
                        >
                            <template x-for="option in filteredOptions" :key="option.id">
                                <li
                                    @click="selectOption(option)"
                                    class="px-4 py-2 cursor-pointer hover:bg-indigo-500 hover:text-white"
                                >
                                    <span x-text="option.name"></span>
                                </li>
                            </template>
                        </ul>

                        {{-- Hidden input for provider ID --}}
                        <input type="hidden" name="provider_id" x-ref="providerId" :value="selectedId" required>
                    </div>


                    <div class="flex justify-end space-x-3">
                        <button type="submit"
                                :disabled="!selectedId"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            Asignar
                        </button>
                        <a href="{{ url()->previous() }}"
                           class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                            Regresar
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        function autocomplete(initialId = null, initialName = '') {
            return {
                query: initialName,
                selectedId: initialId,
                open: false,
                filteredOptions: [],

                async search() {
                    if (this.query.length < 1) {
                        this.filteredOptions = [];
                        return;
                    }

                    try {
                        const res = await fetch(`/logistic-providers/autocomplete?q=${this.query}`);
                        const data = await res.json();
                        this.filteredOptions = data;
                    } catch (error) {
                        console.error(error);
                    }
                },

                selectOption(option) {
                    this.query = option.name;
                    this.selectedId = option.id;
                    this.open = false;
                },

                clear() {
                    this.query = '';
                    this.selectedId = null;
                    this.filteredOptions = [];
                }
            }
        }

    </script>
</x-app-layout>
