<form id="text-form" method="POST" action="{{ route('assistant.text') }}">
    @csrf
    
    <div class="form-group">
        <label for="message">¿En qué puedo ayudarte hoy?</label>
        <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
    </div>
    
    <button type="submit" class="btn btn-primary">
        Enviar pregunta
    </button>
</form>