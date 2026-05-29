<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_revisions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('revised_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->json('before');
            $table->json('after');

            $table->json('changes_summary')->nullable();

            $table->text('note')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index('order_id');
            $table->index('revised_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_revisions');
    }
};