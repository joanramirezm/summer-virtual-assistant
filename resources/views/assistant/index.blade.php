@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Asistente Virtual Summer</div>

                <div class="card-body">
                    @if (session('response'))
                        <div class="alert alert-info">
                            <strong>Summer:</strong> {{ session('response') }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <ul class="nav nav-tabs" id="assistantTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="text-tab" data-toggle="tab" href="#text" role="tab">Texto</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="voice-tab" data-toggle="tab" href="#voice" role="tab">Voz</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="topics-tab" data-toggle="tab" href="#topics" role="tab">Temas de Ayuda</a>
                            </li>
                        </ul>
                        
                        <div class="tab-content mt-3" id="assistantTabsContent">
                            <div class="tab-pane fade show active" id="text" role="tabpanel">
                                @include('assistant.partials.chat')
                            </div>
                            <div class="tab-pane fade" id="voice" role="tabpanel">
                                @include('assistant.partials.voice')
                            </div>
                            <div class="tab-pane fade" id="topics" role="tabpanel">
                                @include('assistant.partials.topics')
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="{{ route('assistant.history') }}" class="btn btn-outline-secondary">
                            Ver historial de conversaciones
                        </a>
                        <a href="{{ route('preferences.edit') }}" class="btn btn-outline-primary float-right">
                            Configurar preferencias
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Activar pestañas
    $('#assistantTabs a').on('click', function (e) {
        e.preventDefault()
        $(this).tab('show')
    });
    
    // Manejar envío de formulario de texto
    $('#text-form').on('submit', function(e) {
        e.preventDefault();
        $(this).submit();
    });
</script>
@endsection