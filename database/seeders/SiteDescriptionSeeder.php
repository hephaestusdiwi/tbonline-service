<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiteDescriptionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('site_settings')->updateOrInsert(
            ['key' => 'site_description'],
            [
                'key'         => 'site_description',
                'value'       => 'Toko vape terpercaya dengan produk original, harga terbaik, dan pelayanan terbaik untuk pengalaman vaping yang lebih maksimal.',
                'type'        => 'text',
                'label'       => 'Deskripsi Situs',
                'description' => 'Deskripsi singkat toko yang ditampilkan di footer website.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );
    }
}