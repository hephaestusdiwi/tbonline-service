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
        Schema::table('complaints', function (Blueprint $table) {
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            $table->text('resolution_note')->nullable()->after('resolved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'customer_phone', 'resolution_note']);
            $table->dropForeign(['resolved_by']);
            $table->dropColumn('resolved_by');
        });
    }
};
