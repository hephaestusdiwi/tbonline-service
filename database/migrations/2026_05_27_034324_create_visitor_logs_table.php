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
        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();

            $table->string('session_id', 64)->index();
            $table->string('ip_address', 45)->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('page', 500)->default('/');
            $table->string('page_title', 255)->nullable();
            $table->string('referrer', 1000)->nullable();
            $table->string('referrer_source', 100)->nullable();

            $table->string('user_agent', 500)->nullable();
            $table->string('browser', 60)->nullable();
            $table->string('browser_version', 30)->nullable();
            $table->string('os', 60)->nullable();
            $table->string('device_type', 20)->nullable();
            $table->string('device_name', 100)->nullable();

            $table->string('country', 100)->nullable();
            $table->string('country_code', 5)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->unsignedSmallInteger('time_on_page')->nullable();
            $table->boolean('is_bounce')->default(false);
            $table->boolean('is_new_visitor')->default(true);

            $table->timestamp('visited_at')->useCurrent()->index();
            $table->timestamps();

            $table->index(['visited_at', 'page']);
            $table->index(['session_id', 'visited_at']);
            $table->index(['device_type', 'visited_at']);
            $table->index(['referrer_source', 'visited_at']);
            $table->index(['country_code', 'visited_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};
