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

        <!-- Controles TTS -->
        <div id="tts-controls" class="mt-4 flex flex-wrap items-center gap-3">
            <button type="button" id="tts-play"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-1.5 px-3 rounded">
                🔊 Escuchar
            </button>
            <button type="button" id="tts-stop"
                class="bg-gray-200 hover:bg-gray-300 text-gray-900 text-sm font-medium py-1.5 px-3 rounded">
                ⏹️ Detener
            </button>

            <label class="text-sm text-gray-700 ml-2">
                Velocidad
                <input id="tts-rate" type="range" min="0.6" max="1.4" step="0.1" value="1"
                       class="align-middle ml-1">
                <span id="tts-rate-val" class="ml-1 text-xs align-middle">1.0x</span>
            </label>

            <label class="text-sm text-gray-700 ml-2">
                Voz
                <select id="tts-voice" class="ml-1 border border-gray-300 rounded px-2 py-1 text-sm">
                    <option value="">(automática)</option>
                </select>
            </label>

            <label class="text-sm text-gray-700 ml-2">
                <input id="tts-autoplay" type="checkbox" class="mr-1" checked>
                Leer automáticamente
            </label>

            <span id="tts-unsupported" class="hidden text-xs text-red-600 ml-2">
                Tu navegador no soporta síntesis de voz.
            </span>
        </div>
    </div>
@endif


                                        <script>
document.addEventListener('DOMContentLoaded', function () {
    // ====== Texto de la respuesta (de sesión) + efecto typewriter ======
    const text = @json(session('response'));
    const el = document.getElementById('typing-text');

    // ====== TTS: helpers y estado ======
    const hasTTS = 'speechSynthesis' in window && 'SpeechSynthesisUtterance' in window;
    const playBtn = document.getElementById('tts-play');
    const stopBtn = document.getElementById('tts-stop');
    const voiceSelect = document.getElementById('tts-voice');
    const rateInput = document.getElementById('tts-rate');
    const rateVal = document.getElementById('tts-rate-val');
    const autoplayChk = document.getElementById('tts-autoplay');
    const unsupported = document.getElementById('tts-unsupported');

    let currentUtterance = null;
    let typingDone = false;
    let i = 0;

    function typeWriter(doneCb) {
        if (!text || !el) return;
        if (i < text.length) {
            el.textContent += text.charAt(i);
            i++;
            setTimeout(() => typeWriter(doneCb), 30);
        } else {
            typingDone = true;
            if (typeof doneCb === 'function') doneCb();
        }
    }

    // ====== TTS core ======
    function cancelTTS() {
        try { window.speechSynthesis.cancel(); } catch (_) {}
        currentUtterance = null;
    }

    function speakTTS(message) {
        if (!hasTTS || !message) return;
        cancelTTS();

        const utt = new SpeechSynthesisUtterance(message);
        // Preferimos voces en español si existen
        const selectedVoiceName = voiceSelect?.value || '';
        const voices = window.speechSynthesis.getVoices();
        let voice = null;

        if (selectedVoiceName) {
            voice = voices.find(v => v.name === selectedVoiceName) || null;
        }
        if (!voice) {
            voice = voices.find(v => v.lang?.toLowerCase().startsWith('es')) ||
                    voices.find(v => v.lang?.toLowerCase().startsWith('en')) ||
                    voices[0] || null;
        }

        if (voice) {
            utt.voice = voice;
            // Alinear lang con la voz
            if (voice.lang) utt.lang = voice.lang;
        } else {
            // fallback
            utt.lang = 'es-ES';
        }

        // Velocidad
        const rate = parseFloat(rateInput?.value || '1') || 1;
        utt.rate = Math.max(0.5, Math.min(2, rate));

        currentUtterance = utt;
        window.speechSynthesis.speak(utt);
    }

    function populateVoices() {
        if (!hasTTS || !voiceSelect) return;
        const voices = window.speechSynthesis.getVoices();
        // Guardar selección actual (si existía)
        const prev = localStorage.getItem('tts.voice') || '';

        // Limpiar y cargar
        voiceSelect.innerHTML = '<option value="">(automática)</option>';
        // Priorizamos español arriba
        const sorted = voices.slice().sort((a, b) => {
            const aEs = a.lang.toLowerCase().startsWith('es') ? -1 : 0;
            const bEs = b.lang.toLowerCase().startsWith('es') ? -1 : 0;
            if (aEs !== bEs) return aEs - bEs;
            return a.name.localeCompare(b.name);
        });

        for (const v of sorted) {
            const opt = document.createElement('option');
            opt.value = v.name;
            opt.textContent = `${v.name} — ${v.lang}`;
            voiceSelect.appendChild(opt);
        }
        if (prev) {
            const exists = Array.from(voiceSelect.options).some(o => o.value === prev);
            if (exists) voiceSelect.value = prev;
        }
    }

    // ====== Bindings UI ======
    if (!hasTTS) {
        if (unsupported) unsupported.classList.remove('hidden');
        if (playBtn) playBtn.disabled = true;
        if (stopBtn) stopBtn.disabled = true;
        if (voiceSelect) voiceSelect.disabled = true;
        if (rateInput) rateInput.disabled = true;
    } else {
        populateVoices();
        window.speechSynthesis.onvoiceschanged = populateVoices;

        // Preferencias locales
        const savedRate = localStorage.getItem('tts.rate');
        const savedAuto = localStorage.getItem('tts.autoplay');

        if (rateInput && savedRate) {
            rateInput.value = savedRate;
            rateVal.textContent = `${parseFloat(savedRate).toFixed(1)}x`;
        }
        if (autoplayChk && savedAuto !== null) {
            autoplayChk.checked = savedAuto === '1';
        }

        voiceSelect?.addEventListener('change', () => {
            localStorage.setItem('tts.voice', voiceSelect.value || '');
        });
        rateInput?.addEventListener('input', () => {
            rateVal.textContent = `${parseFloat(rateInput.value).toFixed(1)}x`;
            localStorage.setItem('tts.rate', rateInput.value);
        });
        autoplayChk?.addEventListener('change', () => {
            localStorage.setItem('tts.autoplay', autoplayChk.checked ? '1' : '0');
        });

        playBtn?.addEventListener('click', () => {
            const message = el?.textContent?.trim() || text || '';
            if (!message) return;
            // Si está en pausa, reanudar; si no, leer desde cero
            if (window.speechSynthesis.paused) {
                window.speechSynthesis.resume();
            } else {
                speakTTS(message);
            }
        });

        stopBtn?.addEventListener('click', cancelTTS);

        // Evitar que quede hablando si salimos
        window.addEventListener('beforeunload', cancelTTS);
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) cancelTTS();
        });
    }

    

    // Inicia: escribe y luego (opcional) lee
if (text && el) {
    // 1️⃣ Decir "Procesando" mientras se escribe
    if (hasTTS && autoplayChk && autoplayChk.checked) {
        speakTTS("Procesando...");
    }

    // 2️⃣ Iniciar efecto typewriter
    typeWriter(() => {
        // 3️⃣ Cuando termine de escribir, leer el texto real
        if (hasTTS && autoplayChk && autoplayChk.checked) {
            speakTTS(text);
        }
    });
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

    // Verificar compatibilidad
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        browserWarning.classList.remove('hidden');
        voiceBtn.disabled = true;
        return;
    }

    let isRecordingFull = false;
    let finalTranscript = '';
    let shortRecognition; // Para detectar la palabra clave
    let fullRecognition;  // Para grabar mensaje completo
    let timeoutId;

    function startShortRecognition() {
        shortRecognition = new SpeechRecognition();
        shortRecognition.continuous = false;
        shortRecognition.interimResults = true;
        shortRecognition.lang = 'es-ES';

        shortRecognition.onresult = function(event) {
            let transcript = '';
            for (let i = event.resultIndex; i < event.results.length; i++) {
                transcript += event.results[i][0].transcript;
            }

            if (transcript.toLowerCase().includes('summer')) {
                // Se dijo la palabra clave
                startFullRecognition();
            }
        };

        shortRecognition.onend = function() {
            if (!isRecordingFull) shortRecognition.start();
        };

        shortRecognition.onerror = function(event) {
            console.error('Error hotword:', event.error);
        };

        shortRecognition.start();
    }

    function startFullRecognition() {
        isRecordingFull = true;
        finalTranscript = '';
        voiceIcon.classList.add('hidden');
        stopIcon.classList.remove('hidden');
        recordingStatus.classList.remove('hidden');
        if (shortRecognition) shortRecognition.stop();

        fullRecognition = new SpeechRecognition();
        fullRecognition.continuous = true;
        fullRecognition.interimResults = true;
        fullRecognition.lang = 'es-ES';

        fullRecognition.onresult = function(event) {
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

            // Detener tras 2s de silencio
            timeoutId = setTimeout(stopFullRecognition, 2000);
        };

        fullRecognition.onerror = function(event) {
            console.error('Error full recording:', event.error);
            stopFullRecognition();
        };

        fullRecognition.start();
    }

    function stopFullRecognition() {
    isRecordingFull = false;
    if (fullRecognition) fullRecognition.stop();
    voiceIcon.classList.remove('hidden');
    stopIcon.classList.add('hidden');
    recordingStatus.classList.add('hidden');

    // Reiniciar detección de hotword
    startShortRecognition();

    // --- Enviar formulario automáticamente después de 1 segundo ---
    setTimeout(() => {
        const form = document.querySelector('form[action="{{ route('assistant.text') }}"]');
        if (form && finalTranscript.trim().length > 0) {
            form.submit();
        }
    }, 1000);
}

    // Botón para grabar manual (opcional)
    voiceBtn.addEventListener('click', function() {
        if (isRecordingFull) {
            stopFullRecognition();
        } else {
            alert('Di "Summer" para empezar a grabar tu mensaje.');
        }
    });

    // Iniciar detección de palabra clave
    startShortRecognition();
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
