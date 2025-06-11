<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AssistantService;
use App\Models\InteractionHistory;

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

        $response = $this->assistantService->handleRequest(
            auth()->user(),
            $request->message,
            'text'
        );

        return back()->with('response', $response);
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
        $histories = auth()->user()->interactionHistory()->latest()->paginate(15);
        return view('assistant.history', compact('histories'));
    }

    public function clearHistory()
    {
        auth()->user()->interactionHistory()->delete();
        return back()->with('status', 'Historial borrado correctamente');
    }
}