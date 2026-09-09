<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dua slot video FIXED buat halaman home (bukan list dinamis kayak
     * Sliders/Categories — nggak ada tambah/hapus, cuma edit isinya):
     * - 'video_only'        → cuma video, tanpa judul/deskripsi
     * - 'video_with_caption'→ video + judul + deskripsi
     *
     * Kedua row langsung di-seed di migration ini biar admin panel selalu
     * punya 2 slot yang bisa diedit dari awal, tanpa perlu form "tambah baru".
     */
    public function up(): void
    {
        Schema::create('home_videos', function (Blueprint $table) {
            $table->id();
            $table->string('slot')->unique();
            $table->string('video_path')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('home_videos')->insert([
            [
                'slot'       => 'video_only',
                'video_path' => null,
                'title'      => null,
                'description'=> null,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slot'       => 'video_with_caption',
                'video_path' => null,
                'title'      => null,
                'description'=> null,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('home_videos');
    }
};