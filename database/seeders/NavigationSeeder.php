<?php

namespace Database\Seeders;

use App\Models\Navigation;
use Illuminate\Database\Seeder;

class NavigationSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['label' => 'Home',         'url' => '/',             'order' => 1],
            ['label' => 'Belanja',      'url' => '/belanja',      'order' => 2],
            ['label' => 'Bundle Deals', 'url' => '/bundle-deals', 'order' => 3],
            ['label' => 'Lokasi Toko',  'url' => '/lokasi-toko',  'order' => 4],
            ['label' => 'TB Point',     'url' => '/tb-point',     'order' => 5],
        ];

        foreach ($menus as $menu) {
            Navigation::create($menu);
        }
    }
}