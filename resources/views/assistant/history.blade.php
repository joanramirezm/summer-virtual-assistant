<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Historial de Interacciones
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Encabezado -->
                <div class="bg-indigo-600 text-white px-6 py-4">
                    <h2 class="text-xl font-bold">Historial</h2>
                </div>

                <!-- Cuerpo -->
                <div class="p-6 space-y-6">
                    @if ($histories->count())
                        @foreach ($histories as $history)
                            <div style="padding: 50px" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition relative">
                                <div class="text-sm text-gray-500 mb-2">
                                    {{ $history->created_at->format('d M Y, h:i A') }}
                                </div>
                                <div class="mb-2">
                                    <span class="font-semibold text-indigo-700">Entrada:</span>
                                    <p class="text-gray-800 mt-1">{{ $history->user_input }}</p>
                                </div>
                                <div>
                                    <span class="font-semibold text-indigo-700">Respuesta:</span>
                                    <p class="text-gray-700 mt-1 whitespace-pre-line">{{ $history->assistant_response }}</p>
                                </div>

                                <!-- Botón eliminar -->
                                <form action="{{ route('assistant.history.destroy', $history->id) }}" method="POST" >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                    style="width: 100px !important; display: flex !important; justify-content: center !important; align-items: center !important; background-color: #f44336 !important; color: #fff !important; margin-top:20px; height: 40px !important; border-radius: 5px !important;"
                                        onclick="return confirm('¿Estás seguro de que deseas eliminar esta interacción?')"
                                        class=""
                                        title="Eliminar interacción">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg> Eliminar
                                    </button>
                                </form>
                            </div>
                        @endforeach

                        <div class="mt-6">
                            {{ $histories->links() }}
                        </div>
                    @else
                        <div class="text-center text-gray-500">
                            No hay interacciones registradas.
                        </div>
                    @endif

                    <div class="mt-8">
                        <a href="{{ route('assistant.index') }}"
                           class="inline-flex items-center px-6 py-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 font-medium rounded-lg transition duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 10h11M3 6h16M3 14h11M3 18h16"></path>
                            </svg>
                            Volver al Asistente
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
