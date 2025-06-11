<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\InteractionHistory;
use App\Models\AssistantResponse;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:admin');
    }

    public function dashboard()
    {
        $stats = [
            'users' => User::count(),
            'interactions' => InteractionHistory::count(),
            'today_interactions' => InteractionHistory::whereDate('created_at', today())->count(),
            'tokens_used' => AssistantResponse::sum('tokens_used')
        ];
        
        $recentInteractions = InteractionHistory::with('user')
            ->latest()
            ->take(10)
            ->get();
            
        $popularTopics = InteractionHistory::select('topic')
            ->selectRaw('count(*) as count')
            ->groupBy('topic')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentInteractions', 'popularTopics'));
    }

    public function users()
    {
        $users = User::withCount('interactionHistory')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('admin.users', compact('users'));
    }

    public function interactions()
    {
        $interactions = InteractionHistory::with('user')
            ->latest()
            ->paginate(20);
            
        return view('admin.interactions', compact('interactions'));
    }
}