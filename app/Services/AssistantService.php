<?php

namespace App\Services;

use App\Models\User;
use App\Models\Preference;
use App\Models\InteractionHistory;
use App\Models\AssistantResponse;
use App\Services\OpenAIService;
use App\Services\VoiceService;

class AssistantService
{
    protected $openAIService;
    protected $voiceService;

    public function __construct(OpenAIService $openAIService, VoiceService $voiceService)
    {
        $this->openAIService = $openAIService;
        $this->voiceService = $voiceService;
    }

    public function handleRequest(User $user, string $input, string $type = 'text')
    {
        // Obtener preferencias del usuario
        $preferences = $user->preference ?? new Preference(Preference::defaultPreferences());

        
        // Procesar la solicitud
        $response = $this->openAIService->generateResponse(
            $input,
            $preferences->language,
            $preferences->tech_skill_level,
            $user->interactionHistory()->latest()->take(5)->get()
        );

        // Guardar interacción en el historial
        $history = InteractionHistory::create([
            'user_id' => $user->id,
            'user_input' => $input,
            'assistant_response' => $response['content'],
            'interaction_type' => $type,
            'language' => $preferences->language,
            'topic' => $response['topic'] ?? 'general'
        ]);

        // Guardar detalles técnicos de la respuesta
        AssistantResponse::create([
            'user_id' => $user->id,
            'prompt' => $input,
            'response' => $response['content'],
            'model_used' => $response['model'],
            'tokens_used' => $response['tokens']
        ]);

        // Si es voz, convertir respuesta a audio
        if ($type === 'voice') {
            return $this->voiceService->textToSpeech($response['content'], $preferences->language);
        }

        return $response['content'];
    }

    public function getLearningTopics()
    {
        return [
            'social_media' => 'Redes Sociales',
            'email' => 'Correo Electrónico',
            'smartphone' => 'Configuración de Smartphone',
            'security' => 'Seguridad Digital',
            'shopping' => 'Compras Online'
        ];
    }
}