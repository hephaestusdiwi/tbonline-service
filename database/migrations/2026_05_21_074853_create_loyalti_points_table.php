<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_points', function (Blueprint $table) {
            $table->id();

            // Identitas user (guest — pakai nomor HP ternormalisasi, contoh: +6281234567890)
            $table->string('phone', 20)->index();

            // Jumlah point dalam batch ini (bisa positif = earn, negatif = deduct/hangus)
            $table->integer('points');

            // Tipe transaksi
            // earn     : didapat dari order sukses
            // expire   : ditarik karena order dibatalkan
            // deduct   : dipakai (manual via admin, untuk masa depan)
            $table->enum('type', ['earn', 'expire', 'deduct']);

            // Referensi ke order terkait (nullable untuk deduct manual)
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');

            // Keterangan opsional (misal: "Order #INV-2024-001" atau "Hangus karena order dibatalkan")
            $table->string('description')->nullable();

            // Kapan point ini kadaluarsa (hanya diisi untuk tipe 'earn', 3 bulan dari created_at)
            $table->timestamp('expired_at')->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_points');
    }
};