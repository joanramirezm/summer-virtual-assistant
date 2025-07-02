<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\PreferenceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('assistant.index');
    })->name('dashboard');
});

Route::middleware(['auth'])->group(function () 
{
    Route::get('/', function () {
        return redirect()->route('assistant.index');
    })->name('home');
    
    // Rutas del asistente
    Route::prefix('assistant')->group(function () {
        Route::get('/', [AssistantController::class, 'index'])->name('assistant.index');
        Route::post('/text', [AssistantController::class, 'handleTextRequest'])->name('assistant.text');
        Route::post('/voice', [AssistantController::class, 'handleVoiceRequest'])->name('assistant.voice');
        Route::get('/history', [AssistantController::class, 'history'])->name('assistant.history');
        Route::delete('/history/{id}', [AssistantController::class, 'destroy'])->name('assistant.history.destroy');
        Route::post('/clear-history', [AssistantController::class, 'clearHistory'])->name('assistant.clear-history');
    });
    
    // Rutas de preferencias
    Route::get('/preferences', [PreferenceController::class, 'edit'])->name('preferences.edit');
    Route::put('/preferences', [PreferenceController::class, 'update'])->name('preferences.update');
});