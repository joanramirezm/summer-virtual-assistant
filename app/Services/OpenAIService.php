<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'timeout'  => 30.0,
        ]);
        $this->apiKey = env('OPENAI_API_KEY');
    }

    public function generateResponse(string $prompt, string $language, string $skillLevel, $history = [])
    {
        try {
            // Construir el contexto basado en el historial
            $context = $this->buildContext($language, $skillLevel, $history);
            
            $response = $this->client->post('chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $context
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 500,
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            return [
                'content' => $data['choices'][0]['message']['content'],
                'model' => 'gpt-3.5-turbo',
                'tokens' => $data['usage']['total_tokens'],
                'topic' => $this->detectTopic($prompt)
            ];

        } catch (\Exception $e) {
            Log::error('Error al conectar con OpenAI: ' . $e->getMessage());
            return [
                'content' => 'Lo siento, estoy teniendo problemas para procesar tu solicitud. Por favor, inténtalo de nuevo más tarde.',
                'model' => 'error',
                'tokens' => 0,
                'topic' => 'error'
            ];
        }
    }

    private function buildContext($language, $skillLevel, $history)
    {
        $context = "Eres Summer, un asistente virtual amigable que ayuda a usuarios con poca experiencia tecnológica. ";
        $context .= "El usuario tiene un nivel de habilidad {$skillLevel} en tecnología. ";
        $context .= "Responde en {$language} de manera clara y sencilla, usando pasos numerados cuando sea necesario. ";
        $context .= "Sé paciente y explicativo. Si no entiendes algo, pide clarificación amablemente.\n\n";
        
        if ($history->isNotEmpty()) {
            $context .= "Historial reciente de conversación:\n";
            foreach ($history as $interaction) {
                $context .= "Usuario: {$interaction->user_input}\n";
                $context .= "Tú: {$interaction->assistant_response}\n\n";
            }
        }
        
        return $context;
    }

    private function detectTopic($prompt)
    {
        $topics = [
            'facebook' => 'social_media',
            'instagram' => 'social_media',
            'whatsapp' => 'social_media',
            'correo' => 'email',
            'gmail' => 'email',
            'outlook' => 'email',
            'celular' => 'smartphone',
            'teléfono' => 'smartphone',
            'contraseña' => 'security',
            'compra' => 'shopping',
            'amazon' => 'shopping'
        ];

        foreach ($topics as $keyword => $topic) {
            if (stripos($prompt, $keyword) !== false) {
                return $topic;
            }
        }

        return 'general';
    }
}