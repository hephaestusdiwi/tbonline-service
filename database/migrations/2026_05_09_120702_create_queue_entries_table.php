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
        Schema::create('queue_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('chat_sessions')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->enum('status', ['waiting', 'assigned', 'cancelled'])->default('waiting');
            $table->unsignedInteger('estimated_wait_seconds')->nullable();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('assigned_at')->nullable();

            $table->index(['status', 'joined_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_entries');
    }
};
