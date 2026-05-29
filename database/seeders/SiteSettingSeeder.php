<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [
                'key'         => 'site_logo',
                'value'       => null,
                'type'        => 'image',
                'label'       => 'Logo Website',
                'description' => 'Logo utama yang tampil di header dan halaman publik.',
            ],
            [
                'key'         => 'site_name',
                'value'       => 'My App',
                'type'        => 'text',
                'label'       => 'Nama website',
                'description' => 'Nama website yang tampil di browser dan tab dan navbar',
            ],
            [
                'key'         => 'site_favicon',
                'value'       => null,
                'type'        => 'image',
                'label'       => 'Favicon',
                'description' => 'Icon kecil di browser tab (PNG, rekomendasi 32x32px)',
            ],
            [
                'key'         => 'google_site_verification',
                'value'       => null,
                'type'        => 'Google Site Verification',
                'description' => 'Kode verifikasi dari Google Search Console',
            ],
        ];

        foreach ($defaults as $setting) {
            SiteSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}