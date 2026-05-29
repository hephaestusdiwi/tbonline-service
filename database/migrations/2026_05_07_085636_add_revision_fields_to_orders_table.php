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
        Schema::table('orders', function (Blueprint $table) {
            // pastikan kolom ini ada
            if (!Schema::hasColumn('orders', 'promo_code')) {
                $table->string('promo_code')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->unsignedBigInteger('discount_amount')->default(0)->after('promo_code');
            }

            // Kolom baru untuk tracking revisi
            $table->timestamp('revised_at')->nullable()->after('discount_amount');
            $table->foreignId('revised_by')->nullable()->constrained('users')->nullOnDelete()->after('revised_at');
            $table->unsignedSmallInteger('revision_count')->default(0)->after('revised_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['revised_at', 'revision_count']);

            if (Schema::hasColumn('orders', 'revised_by')) {
                $table->dropForeign(['revised_by']);
                $table->dropColumn('revised_by');
            }
        });
    }
};
