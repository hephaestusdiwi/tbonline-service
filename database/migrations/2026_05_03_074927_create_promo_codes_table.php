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
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed', 'free_shipping']);
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->unsignedBigInteger('min_purchase')->default(0);
            $table->unsignedInteger('max_usage')->nullable(); // null = unlimited
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });

        // tambah kolom promo ke table orders yang udah ada
        Schema::table('orders', function (Blueprint $table) {
            $table->string('promo_code')->nullable()->after('shipping_cost');
            $table->unsignedBigInteger('discount_amount')->default(0)->after('promo_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['promo_code', 'discount_amount']);
        });
        Schema::dropIfExists('promo_codes');
    }
};
