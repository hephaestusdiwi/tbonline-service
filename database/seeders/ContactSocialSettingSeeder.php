<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class ContactSocialSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // ── Kontak ──────────────────────────────────────────────────────
            [
                'key'         => 'contact_address',
                'value'       => null,
                'type'        => 'textarea',
                'label'       => 'Alamat',
                'description' => 'Alamat lengkap yang tampil di footer website',
            ],
            [
                'key'         => 'contact_phone',
                'value'       => null,
                'type'        => 'text',
                'label'       => 'Nomor HP / Telepon',
                'description' => 'Nomor telepon yang tampil di footer website',
            ],
            [
                'key'         => 'contact_email',
                'value'       => null,
                'type'        => 'text',
                'label'       => 'Email',
                'description' => 'Alamat email yang tampil di footer website',
            ],
            [
                'key'         => 'contact_whatsapp',
                'value'       => null,
                'type'        => 'text',
                'label'       => 'WhatsApp',
                'description' => 'Nomor WhatsApp untuk link wa.me (format internasional)',
            ],

            // ── Media Sosial ─────────────────────────────────────────────────
            [
                'key'         => 'social_facebook',
                'value'       => null,
                'type'        => 'url',
                'label'       => 'Facebook',
                'description' => 'URL halaman Facebook',
            ],
            [
                'key'         => 'social_instagram',
                'value'       => null,
                'type'        => 'url',
                'label'       => 'Instagram',
                'description' => 'URL profil Instagram',
            ],
            [
                'key'         => 'social_tiktok',
                'value'       => null,
                'type'        => 'url',
                'label'       => 'TikTok',
                'description' => 'URL akun TikTok',
            ],
            [
                'key'         => 'social_twitter',
                'value'       => null,
                'type'        => 'url',
                'label'       => 'Twitter / X',
                'description' => 'URL akun Twitter / X',
            ],
            [
                'key'         => 'social_youtube',
                'value'       => null,
                'type'        => 'url',
                'label'       => 'YouTube',
                'description' => 'URL saluran YouTube',
            ],
            [
                'key'         => 'social_linkedin',
                'value'       => null,
                'type'        => 'url',
                'label'       => 'LinkedIn',
                'description' => 'URL halaman LinkedIn',
            ],
        ];

        foreach ($defaults as $setting) {
            SiteSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}