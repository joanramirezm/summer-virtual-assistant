<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('assistant_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('prompt');
            $table->text('response');
            $table->string('model_used')->default('gpt-3.5-turbo');
            $table->integer('tokens_used');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assistant_responses');
    }
};