<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chatbot_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->unique()->constrained('chat_sessions')->cascadeOnDelete();
            $table->string('current_node')->default('greeting');
            $table->json('context')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->boolean('needs_agent')->default(false);
            $table->timestamp('handed_off_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_sessions');
    }
};
