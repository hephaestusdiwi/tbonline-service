<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $row = DB::table('site_settings')
            ->where('key', 'shipping_couriers')
            ->first();

        if (!$row || empty($row->value)) {
            return;
        }

        $couriers = json_decode($row->value, true);

        if (!is_array($couriers)) {
            return;
        }

        $codeMap = $this->buildCodeMap();
        $updated = false;

        foreach ($couriers as &$courier) {
            if (!is_array($courier)) {
                continue;
            }

            // Jangan overwrite code yang sudah ada.
            if (!empty($courier['code'])) {
                continue;
            }

            $normalized = $this->normalize($courier['name'] ?? '');

            if ($normalized !== '' && isset($codeMap[$normalized])) {
                $courier['code'] = $codeMap[$normalized];
                $courier['service'] = $courier['service'] ?? '';
                $updated = true;
            }
        }

        unset($courier);

        if ($updated) {
            DB::table('site_settings')
                ->where('key', 'shipping_couriers')
                ->update([
                    'value' => json_encode($couriers),
                ]);
        }
    }

    public function down(): void
    {
        // Backfill sengaja tidak di-revert agar data existing
        // tidak terhapus atau kehilangan code yang sudah diisi.
    }

    private function normalize(string $name): string
    {
        $name = strtolower($name);
        $name = str_replace(['&', '.', '-', '_'], ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name);

        return trim($name);
    }

    private function buildCodeMap(): array
    {
        $map = [];

        foreach (config('rajaongkir.couriers', []) as $code => $name) {
            $map[$this->normalize($name)] = $code;
            $map[$this->normalize($code)] = $code;
        }

        $aliases = [
            'jne'            => 'jne',
            'jne express'    => 'jne',

            'jnt'            => 'jnt',
            'j t'            => 'jnt',
            'j t express'    => 'jnt',
            'jnt express'    => 'jnt',

            'sicepat'        => 'sicepat',
            'si cepat'       => 'sicepat',

            'anteraja'       => 'anteraja',
            'anter aja'      => 'anteraja',

            'ninja'          => 'ninja',
            'ninja express'  => 'ninja',
            'ninja xpress'   => 'ninja',

            'id express'     => 'ide',
            'idexpress'      => 'ide',

            'sap express'    => 'sap',

            'lion parcel'    => 'lion',

            'ncs'            => 'ncs',

            'rex express'    => 'rex',

            'rpx'            => 'rpx',
            'rpx holding'    => 'rpx',

            'wahana'         => 'wahana',
            'wahana express' => 'wahana',

            'sentral cargo'  => 'sentral',
            'star cargo'     => 'star',

            '21 express'     => 'dse',
            'dse'            => 'dse',

            'tiki'           => 'tiki',

            'pos'            => 'pos',
            'pos indonesia'  => 'pos',
            'kantor pos'     => 'pos',
        ];

        return array_merge($map, $aliases);
    }
};
