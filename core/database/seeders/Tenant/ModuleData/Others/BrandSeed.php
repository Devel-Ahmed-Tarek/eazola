<?php

namespace Database\Seeders\Tenant\ModuleData\Others;

use App\Facades\GlobalLanguage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeed extends Seeder
{
    public static function execute()
    {
        try {
            $slug = GlobalLanguage::default_slug();
        } catch (\Throwable $e) {
            $slug = 'en';
        }

        $urlHash = json_encode([$slug => '#'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $urlS = json_encode([$slug => 's'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $now = now();

        DB::table('brands')->insert([
            ['url' => $urlHash, 'image' => '77', 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['url' => $urlHash, 'image' => '76', 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['url' => $urlHash, 'image' => '75', 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['url' => $urlHash, 'image' => '74', 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['url' => $urlHash, 'image' => '73', 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['url' => $urlHash, 'image' => '72', 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['url' => $urlS, 'image' => '72', 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
