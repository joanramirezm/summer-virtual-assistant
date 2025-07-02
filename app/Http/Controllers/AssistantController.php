<?php

namespace App\Http\Controllers;

use App\Models\Preference;
use Illuminate\Http\Request;
use App\Models\InteractionHistory;
use App\Services\AssistantService;

class AssistantController extends Controller
{
    protected $assistantService;

    public function __construct(AssistantService $assistantService)
    {
        $this->assistantService = $assistantService;
        $this->middleware('auth');
    }

    public function index()
    {
        $histories = auth()->user()->interactionHistory()->latest()->take(10)->get();
        $topics = $this->assistantService->getLearningTopics();
        
        return view('assistant.index', compact('histories', 'topics'));
    }

    public function handleTextRequest(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $respuesta = $this->consultarOpenAI($request->message);

        $user = auth()->user();

        $input = $request->message;

        $type = 'text';

        $preferences = $user->preference ?? new Preference(Preference::defaultPreferences());

        // Guardar interacción en el historial
        $history = InteractionHistory::create([
            'user_id' => $user->id,
            'user_input' => $input,
            'assistant_response' => $respuesta,
            'interaction_type' => $type,
            'language' => $user->preference->language ?? 'es',
            'topic' => 'general'
        ]);


        return back()->with('response', $respuesta);
    }


    private function consultarOpenAI($mensajeUsuario)
    {
        $apiKey = '';

        $url = 'https://api.openai.com/v1/chat/completions';

        $data = [
            'model' => 'gpt-4', // o 'gpt-3.5-turbo'
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Eres un asistente útil.'
                ],
                [
                    'role' => 'user',
                    'content' => $mensajeUsuario
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000,
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Error en la solicitud CURL: ' . curl_error($ch));
        }

        curl_close($ch);

        $decoded = json_decode($response, true);

        return $decoded['choices'][0]['message']['content'] ?? 'No se recibió respuesta';
    }

    public function handleVoiceRequest(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav'
        ]);

        $audioPath = $request->file('audio')->store('voice_input');
        $transcript = $this->assistantService->getVoiceService()->speechToText($audioPath);
        
        $response = $this->assistantService->handleRequest(
            auth()->user(),
            $transcript,
            'voice'
        );

        return response()->json([
            'response' => $response,
            'audio_url' => $response // En este caso, el servicio devuelve directamente la URL del audio
        ]);
    }

    public function history()
    {
        $histories = auth()->user()->interactionHistory()->latest()->paginate(2);
        return view('assistant.history', compact('histories'));
    }

    public function clearHistory()
    {
        auth()->user()->interactionHistory()->delete();
        return back()->with('status', 'Historial borrado correctamente');
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $history = $user->interactionHistory()->findOrFail($id);
        $history->delete();

        return redirect()->route('assistant.history')->with('success', 'Interacción eliminada correctamente.');
    }
}