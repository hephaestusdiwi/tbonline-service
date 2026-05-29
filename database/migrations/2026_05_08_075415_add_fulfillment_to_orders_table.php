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
            $table->enum('fulfillment_type', ['delivery', 'pickup'])
                  ->default('delivery')
                  ->after('notes');

            $table->unsignedBigInteger('branch_id')
                  ->nullable()
                  ->after('fulfillment_type');

            $table->foreign('branch_id')
                  ->references('id')
                  ->on('branches')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['fulfillment_type', 'branch_id']);
        });
    }
};
