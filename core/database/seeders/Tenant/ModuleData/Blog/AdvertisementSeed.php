<?php

namespace Database\Seeders\Tenant\ModuleData\Blog;

use App\Facades\GlobalLanguage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdvertisementSeed extends Seeder
{
    public static function execute()
    {
        try {
            $slug = GlobalLanguage::default_slug();
        } catch (\Throwable $e) {
            $slug = 'en';
        }

        $t = function (string $s) use ($slug) {
            return json_encode([$slug => $s], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        };

        DB::table('advertisements')->insert([
            ['id' => 2, 'type' => 'image', 'size' => '950*160', 'image' => '321', 'slot' => '4379939651', 'embed_code' => null, 'redirect_url' => '#', 'click' => 2, 'impression' => 0, 'status' => 1, 'created_at' => '2021-10-17 14:33:13', 'updated_at' => '2023-01-16 07:19:03', 'title' => $t('Travel Advertisement Google Adsense')],
            ['id' => 3, 'type' => 'scripts', 'size' => '728*90', 'image' => null, 'slot' => null, 'embed_code' => '', 'redirect_url' => '', 'click' => 0, 'impression' => 5, 'status' => 1, 'created_at' => '2021-10-17 14:35:32', 'updated_at' => '2021-11-14 11:13:21', 'title' => $t('Fashion Advertisement  Custom Scripts')],
            ['id' => 8, 'type' => 'image', 'size' => '950*160', 'image' => '324', 'slot' => null, 'embed_code' => null, 'redirect_url' => '#', 'click' => 0, 'impression' => 1, 'status' => 1, 'created_at' => '2021-11-03 11:55:16', 'updated_at' => '2023-01-16 07:18:56', 'title' => $t('Festival')],
            ['id' => 9, 'type' => 'image', 'size' => '950*200', 'image' => '333', 'slot' => null, 'embed_code' => null, 'redirect_url' => '#', 'click' => 10, 'impression' => 109, 'status' => 1, 'created_at' => '2021-11-13 19:22:52', 'updated_at' => '2023-01-16 14:06:08', 'title' => $t('Advertisement Two')],
            ['id' => 10, 'type' => 'image', 'size' => '950*200', 'image' => '321', 'slot' => null, 'embed_code' => null, 'redirect_url' => '#', 'click' => 10, 'impression' => 141, 'status' => 1, 'created_at' => '2021-11-13 19:24:17', 'updated_at' => '2023-01-16 14:03:42', 'title' => $t('Advertisement Three')],
            ['id' => 11, 'type' => 'scripts', 'size' => '250*1110', 'image' => null, 'slot' => null, 'embed_code' => '', 'redirect_url' => '', 'click' => 0, 'impression' => 0, 'status' => 1, 'created_at' => '2021-11-14 16:30:32', 'updated_at' => '2021-11-14 16:31:45', 'title' => $t('Script Test 2')],
            ['id' => 13, 'type' => 'image', 'size' => '300*600', 'image' => '319', 'slot' => null, 'embed_code' => null, 'redirect_url' => '#', 'click' => 0, 'impression' => 0, 'status' => 1, 'created_at' => '2023-01-16 11:06:33', 'updated_at' => '2023-01-16 11:06:33', 'title' => $t('Sidebar Add')],
        ]);
    }
}
