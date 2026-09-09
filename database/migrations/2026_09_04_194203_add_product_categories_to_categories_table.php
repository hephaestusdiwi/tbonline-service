<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu tile kategori di homepage sekarang bisa mewakili BEBERAPA
     * kategori produk sekaligus (mis. tile "Liquid" nyakup "Liquid
     * Freebase", "Liquid Saltnic", "Liquid Import"). 'name' tetap jadi
     * label tampilan tile; 'product_categories' nyimpen daftar kategori
     * produk mentah yang digabung ke tile itu.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->json('product_categories')->nullable()->after('name');
        });

        // Backfill kategori yang udah ada: anggap product_categories-nya
        // cuma [name] sendiri, biar behavior lama (1 tile = 1 kategori
        // produk persis) tetap jalan tanpa perlu admin edit ulang satu-satu.
        DB::table('categories')->whereNull('product_categories')->get(['id', 'name'])
            ->each(function ($row) {
                DB::table('categories')->where('id', $row->id)->update([
                    'product_categories' => json_encode([$row->name]),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('product_categories');
        });
    }
};