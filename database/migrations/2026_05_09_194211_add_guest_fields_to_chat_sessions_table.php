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
    Schema::table('chat_sessions', function (Blueprint $table) {
        $table->foreignId('customer_id')->nullable()->change(); // jadikan nullable
        $table->string('guest_name')->nullable()->after('customer_id');
        $table->string('guest_email')->nullable()->after('guest_name');
        $table->string('guest_token')->nullable()->unique()->after('guest_email'); // untuk identify guest
    });
}

    /**
     * Reverse the migrations.
     */
public function down(): void
{
    Schema::table('chat_sessions', function (Blueprint $table) {
        $table->dropColumn(['guest_name', 'guest_email', 'guest_token']);
    });
}
};
