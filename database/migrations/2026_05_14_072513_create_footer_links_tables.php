<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Footer Link Groups ──────────────────────────────────────────────
        Schema::create('footer_link_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // e.g. "About Us", "Customer Service"
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ── Footer Links ────────────────────────────────────────────────────
        Schema::create('footer_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('footer_link_group_id')
                  ->constrained('footer_link_groups')
                  ->cascadeOnDelete();
            $table->string('label');          // e.g. "Our Story"
            $table->string('url');            // e.g. "/about" or "https://..."
            $table->boolean('open_new_tab')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_links');
        Schema::dropIfExists('footer_link_groups');
    }
};