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
        Schema::create('order_revision', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('revised_by')->constrained('users');

            $table->json('before')->nullable();

            $table->json('after')->nullable();

            $table->json('changes_summary')->nullable()
                  ->comment('Array of strings describing what changed');

            $table->text('note')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index('order_id');
            $table->index('revised_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_revision');
    }
};
