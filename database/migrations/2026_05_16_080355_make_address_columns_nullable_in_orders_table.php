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
            $table->text('address')->nullable()->change();
            $table->string('subdistrict')->nullable()->change();
            $table->string('district')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('province')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('address')->nullable(false)->change();
            $table->string('subdistrict')->nullable(false)->change();
            $table->string('district')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
            $table->string('province')->nullable(false)->change();
        });
    }
};
