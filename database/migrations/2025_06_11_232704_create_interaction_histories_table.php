<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('interaction_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('user_input');
            $table->text('assistant_response');
            $table->string('interaction_type'); // text or voice
            $table->string('language');
            $table->string('topic');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('interaction_histories');
    }
};