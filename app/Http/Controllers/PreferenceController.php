<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Preference;
use App\Services\AssistantService;

class PreferenceController extends Controller
{
    public function edit()
    {
        $preferences = auth()->user()->preferences ?? new Preference();
        $languages = ['es' => 'Español', 'en' => 'English'];
        $interfaces = ['text' => 'Texto', 'voice' => 'Voz'];
        $skillLevels = [
            'beginner' => 'Principiante',
            'intermediate' => 'Intermedio',
            'advanced' => 'Avanzado'
        ];
        
        
        return view('preferences.edit', compact(
            'preferences',
            'languages',
            'interfaces',
            'skillLevels',
        ));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'language' => 'required|in:es,en',
            'preferred_interface' => 'required|in:text,voice',
            'tech_skill_level' => 'required|in:beginner,intermediate,advanced',
            'learning_topics' => 'nullable|array'
        ]);

        $user = auth()->user();
        
        if ($user->preferences) {
            $user->preferences()->update($validated);
        } else {
            $user->preferences()->create($validated);
        }

        return redirect()->route('assistant.index')
            ->with('status', 'Preferencias actualizadas correctamente');
    }
}