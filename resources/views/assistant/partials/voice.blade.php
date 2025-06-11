<div class="text-center">
    <button id="startRecording" class="btn btn-danger btn-lg rounded-circle mb-3">
        <i class="fas fa-microphone"></i>
    </button>
    <p class="text-muted">Presiona el botón y habla</p>
    
    <div id="voiceResponse" class="mt-3" style="display: none;">
        <audio id="responseAudio" controls></audio>
    </div>
</div>

@section('scripts')
<script>
    let mediaRecorder;
    let audioChunks = [];
    
    $('#startRecording').on('mousedown touchstart', function() {
        $(this).html('<i class="fas fa-stop"></i>');
        startRecording();
    });
    
    $('#startRecording').on('mouseup touchend', function() {
        $(this).html('<i class="fas fa-microphone"></i>');
        stopRecording();
    });
    
    function startRecording() {
        audioChunks = [];
        navigator.mediaDevices.getUserMedia({ audio: true })
            .then(stream => {
                mediaRecorder = new MediaRecorder(stream);
                mediaRecorder.start();
                
                mediaRecorder.ondataavailable = event => {
                    audioChunks.push(event.data);
                };
            })
            .catch(error => {
                console.error('Error al acceder al micrófono:', error);
                alert('No se pudo acceder al micrófono. Por favor, verifica los permisos.');
            });
    }
    
    function stopRecording() {
        mediaRecorder.stop();
        mediaRecorder.onstop = () => {
            const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
            sendAudioToServer(audioBlob);
        };
    }
    
    function sendAudioToServer(audioBlob) {
        const formData = new FormData();
        formData.append('audio', audioBlob, 'recording.wav');
        formData.append('_token', '{{ csrf_token() }}');
        
        fetch('{{ route("assistant.voice") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.audio_url) {
                const audioPlayer = document.getElementById('responseAudio');
                audioPlayer.src = data.audio_url;
                document.getElementById('voiceResponse').style.display = 'block';
                audioPlayer.play();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la voz. Intenta nuevamente.');
        });
    }
</script>
@endsection