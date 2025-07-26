<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Asistente Virtual Summer
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-center">
                <div class="w-full">
                    <!-- Tarjeta principal -->
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <!-- Encabezado -->
                        <div class="bg-indigo-600 text-white px-6 py-4">
                            <h2 class="text-xl font-bold">Asistente Virtual</h2>
                        </div>

                        <!-- Cuerpo -->
                        <div class="p-6">

                            <!-- Pestañas -->
                            <div class="mb-6">
                                <div class="border-b border-gray-200">
                                    <nav class="flex -mb-px">
                                        <a href="#text" id="text-tab" data-toggle="tab" role="tab"
                                            class="mr-2 py-4 px-6 text-center border-b-2 font-medium text-sm border-indigo-500 text-indigo-600">
                                            Texto
                                        </a>
                                    </nav>
                                </div>

                                <!-- Contenido de pestañas -->
                                <div class="py-4">
                                    <!-- Pestaña Texto -->
                                    <div id="text" role="tabpanel" class="tab-pane active">

                                        <!-- Mensaje de respuesta -->
                                        @if (session('response'))
                                            <div style="padding: 20px"
                                                class=" bg-indigo-50 border-l-4 border-indigo-500 text-indigo-700 p-4 mb-6 rounded-r">
                                                <p class="font-semibold">Repuesta:</p>
                                                <p class="mt-1" id="typing-text"></p>
                                            </div>
                                        @endif


                                        <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                const text = @json(session('response'));
                                                const el = document.getElementById('typing-text');

                                                let i = 0;

                                                function typeWriter() {
                                                    if (i < text.length) {
                                                        el.textContent += text.charAt(i);
                                                        i++;
                                                        setTimeout(typeWriter, 30);
                                                    }
                                                }

                                                if (text) {
                                                    typeWriter();
                                                }
                                            });
                                        </script>

                                        <form action="{{ route('assistant.text') }}" method="post" class="space-y-4">
                                            @csrf
                                            <div class="relative">
                                                <textarea
                                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                                                    rows="4" placeholder="Escribe tu mensaje aquí o haz clic en el micrófono..." name="message"
                                                    id="message-input"></textarea>
                                                <button type="button" id="voice-btn"
                                                    class="absolute right-3 bottom-3 p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition">
                                                    <svg id="voice-icon" xmlns="http://www.w3.org/2000/svg"
                                                        class="h-6 w-6 text-indigo-600" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                                    </svg>
                                                    <svg id="stop-icon" xmlns="http://www.w3.org/2000/svg"
                                                        class="h-6 w-6 text-red-600 hidden" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                                                    </svg>
                                                </button>
                                                <span id="recording-status"
                                                    class="hidden absolute right-3 top-3 text-xs text-red-600 font-medium bg-white px-2 py-1 rounded">Grabando...
                                                    habla ahora</span>
                                                <span id="browser-warning"
                                                    class="hidden absolute right-3 top-3 text-xs text-red-600 font-medium bg-white px-2 py-1 rounded">Funcionalidad
                                                    solo disponible en Chrome/Edge</span>
                                            </div>

                                            <script>
                                                document.addEventListener('DOMContentLoaded', function() {
                                                const voiceBtn = document.getElementById('voice-btn');
                                                const messageInput = document.getElementById('message-input');
                                                const voiceIcon = document.getElementById('voice-icon');
                                                const stopIcon = document.getElementById('stop-icon');
                                                const recordingStatus = document.getElementById('recording-status');
                                                const browserWarning = document.getElementById('browser-warning');

                                                // Verificar compatibilidad del navegador
                                                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                                                if (!SpeechRecognition) {
                                                    browserWarning.classList.remove('hidden');
                                                    voiceBtn.disabled = true;
                                                    return;
                                                }

                                                const recognition = new SpeechRecognition();
                                                recognition.continuous = true;
                                                recognition.interimResults = true;
                                                recognition.lang = 'es-ES';

                                                let isRecording = false;
                                                let finalTranscript = '';
                                                let timeoutId;

                                                recognition.onstart = function() {
                                                    isRecording = true;
                                                    voiceIcon.classList.add('hidden');
                                                    stopIcon.classList.remove('hidden');
                                                    recordingStatus.classList.remove('hidden');
                                                    finalTranscript = messageInput.value; // Mantener el texto existente
                                                };

                                                recognition.onresult = function(event) {
                                                    clearTimeout(timeoutId);
                                                    let interimTranscript = '';
                                                    for (let i = event.resultIndex; i < event.results.length; i++) {
                                                        const transcript = event.results[i][0].transcript;
                                                        if (event.results[i].isFinal) {
                                                            finalTranscript += transcript + ' ';
                                                        } else {
                                                            interimTranscript += transcript;
                                                        }
                                                    }
                                                    messageInput.value = finalTranscript + interimTranscript;

                                                    timeoutId = setTimeout(() => {
                                                        if (isRecording) stopRecording();
                                                    }, 3000); // Detener después de 3 segundos de inactividad
                                                };

                                            recognition.onerror = function(event) {
                                                console.error('Error:', event.error);
                                                stopRecording();

                                                if (event.error === 'not-allowed') {
                                                    alert('Por favor permite el acceso al micrófono en la configuración de tu navegador');
                                                } else if (event.error === 'network') {
                                                    alert('Error de conexión. Por favor, verifica tu red.');
                                                }
                                            };


                                                recognition.onend = function() {
                                                    console.log('Reconocimiento finalizado');
                                                    if (isRecording) {
                                                        recognition.start(); // Reiniciar si aún estamos grabando
                                                    }
                                                };

                                                function startRecording() {
                                                    try {
                                                        recognition.start();
                                                    } catch (e) {
                                                        console.error(e);
                                                    }
                                                }

                                                function stopRecording() {
                                                    isRecording = false;
                                                    recognition.stop();
                                                    voiceIcon.classList.remove('hidden');
                                                    stopIcon.classList.add('hidden');
                                                    recordingStatus.classList.add('hidden');
                                                    clearTimeout(timeoutId);
                                                }

                                                voiceBtn.addEventListener('click', function() {
                                                    if (isRecording) {
                                                        stopRecording();
                                                    } else {
                                                        startRecording();
                                                    }
                                                });
                                            });

                                            </script>
                                            <button
                                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                                                Enviar
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Pestaña Voz -->
                                    <div id="voice" role="tabpanel" class="tab-pane hidden">
                                        <div class="space-y-4 text-center">
                                            <div class="p-6 bg-gray-50 rounded-lg">
                                                <button
                                                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-8 rounded-full transition duration-200 flex items-center mx-auto">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z">
                                                        </path>
                                                    </svg>
                                                    Hablar con Summer
                                                </button>
                                                <p class="mt-4 text-gray-600">Presiona el botón y habla claramente</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pestaña Temas -->
                                    <div id="topics" role="tabpanel" class="tab-pane hidden">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div
                                                class="p-4 border border-gray-200 rounded-lg hover:bg-indigo-50 transition duration-200">
                                                <h3 class="font-semibold text-indigo-700">Configuración</h3>
                                                <p class="text-gray-600 mt-1">Ayuda con preferencias y ajustes</p>
                                            </div>
                                            <div
                                                class="p-4 border border-gray-200 rounded-lg hover:bg-indigo-50 transition duration-200">
                                                <h3 class="font-semibold text-indigo-700">Facturación</h3>
                                                <p class="text-gray-600 mt-1">Preguntas sobre pagos y suscripciones</p>
                                            </div>
                                            <div
                                                class="p-4 border border-gray-200 rounded-lg hover:bg-indigo-50 transition duration-200">
                                                <h3 class="font-semibold text-indigo-700">Funcionalidades</h3>
                                                <p class="text-gray-600 mt-1">Cómo usar las herramientas disponibles</p>
                                            </div>
                                            <div
                                                class="p-4 border border-gray-200 rounded-lg hover:bg-indigo-50 transition duration-200">
                                                <h3 class="font-semibold text-indigo-700">Soporte Técnico</h3>
                                                <p class="text-gray-600 mt-1">Problemas con la plataforma</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones inferiores -->
                            <div class="mt-6 flex flex-col sm:flex-row justify-between gap-4">
                                <a href="{{ route('assistant.history') }}"
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-6 rounded-lg transition duration-200 text-center">
                                    <div class="flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Ver historial
                                    </div>
                                </a>
                                <a href="{{ route('preferences.edit') }}"
                                    class="bg-indigo-100 hover:bg-indigo-200 text-indigo-700 font-medium py-2 px-6 rounded-lg transition duration-200 text-center">
                                    <div class="flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                            </path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Configurar preferencias
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
