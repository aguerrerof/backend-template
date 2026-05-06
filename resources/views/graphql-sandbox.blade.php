<!-- resources/views/graphql-sandbox.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            GraphQL Sandbox Shopify
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow-md rounded-md p-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-4">📝 Consulta GraphQL</h3>

                    <form method="POST" action="{{ route('graphql-sandbox.execute') }}" class="space-y-4">
                        @csrf

                        <div class="flex items-center space-x-2">
                            <input type="checkbox" name="use_admin_api" value="1"
                                   class="form-checkbox h-5 w-5 text-blue-600"
                                {{ old('use_admin_api', $use_admin_api ?? false) ? 'checked' : '' }}>
                            <label class="text-gray-700 font-medium">Usar Admin API</label>
                        </div>

                        <div>
                            <label class="font-semibold text-gray-700 mb-1 block">Consulta:</label>
                            <textarea name="query" rows="10"
                                      class="w-full p-4 border rounded-md font-mono text-sm bg-gray-50 focus:ring focus:ring-blue-200"
                                      placeholder="Escribe tu consulta GraphQL aquí..." required>{{ old('query', $query ?? '') }}</textarea>
                        </div>

                        <div>
                            <label class="font-semibold text-gray-700 mb-1 block">Variables (JSON):</label>
                            <textarea name="variables" rows="5"
                                      class="w-full p-4 border rounded-md font-mono text-sm bg-gray-50 focus:ring focus:ring-blue-200"
                                      placeholder='{"query":"tag:shirt","after":null}' required>{{ old('variables', $variables ?? '') }}</textarea>
                        </div>

                        <button type="submit"
                                class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Ejecutar
                        </button>
                    </form>
                </div>
                <div class="bg-white shadow-md rounded-md p-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-4">📊 Resultados</h3>

                    @if(isset($exception_message) || !empty($exception_message))
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-md mb-4">
                            <h4 class="font-bold text-red-800 mb-2">❌ Error de ejecución</h4>
                            <pre class="text-sm text-red-700 overflow-x-auto">{{ $exception_message }}</pre>
                        </div>
                    @endif

                    @if(isset($errors))
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-md mb-4">
                            <h4 class="font-bold text-yellow-800 mb-2">⚠️ Errores de GraphQL</h4>
                            <pre class="text-sm text-yellow-700 overflow-x-auto">{{ $errors }}</pre>
                        </div>
                    @endif

                    @if(isset($result))
                        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-md">
                            <h4 class="font-bold text-green-800 mb-2">✅ Resultado</h4>
                            <pre class="text-sm text-gray-800 overflow-x-auto">{{ $result }}</pre>
                        </div>
                    @endif

                    @if(!isset($result) && !isset($errors) && !isset($exception_message))
                        <p class="text-gray-500">Los resultados aparecerán aquí después de ejecutar la consulta.</p>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
