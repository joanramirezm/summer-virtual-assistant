<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AssistantService;
use App\Services\OpenAIService;
use App\Services\VoiceService;

class AssistantServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(OpenAIService::class, function ($app) {
            return new OpenAIService();
        });
        
        $this->app->singleton(VoiceService::class, function ($app) {
            return new VoiceService();
        });
        
        $this->app->singleton(AssistantService::class, function ($app) {
            return new AssistantService(
                $app->make(OpenAIService::class),
                $app->make(VoiceService::class)
            );
        });
    }

    public function boot()
    {
        //
    }
}