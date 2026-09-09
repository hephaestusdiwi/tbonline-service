<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Singleton settings buat section Flash Sale di homepage — cuma 1 row,
     * mirip pola HomeVideo (bukan list dinamis). is_active = toggle on/off
     * gampang dari admin panel, ends_at = kapan countdown-nya abis.
     */
    public function up(): void
    {
        Schema::create('flash_sales', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(false);
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        DB::table('flash_sales')->insert([
            'is_active'  => false,
            'ends_at'    => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('flash_sales');
    }
};