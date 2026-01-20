<?php

namespace Modules\Restaurant\Database\Seeders;
use Illuminate\Database\Seeder;


class RestaurantLayoutSeeder extends Seeder
{
    public static function run()
    {
        $only_path = 'assets/tenant/page-layout/home-pages/restaurant-layout.json';
        if (!file_exists($only_path) && !is_dir($only_path) && moduleExists('Restaurant')) {
            $restaurant_layout_path = 'core/Modules/Restaurant/assets/page-layout/home-page/restaurant-layout.json';
            $restaurant_layout_content = file_get_contents($restaurant_layout_path);
            file_put_contents($only_path, $restaurant_layout_content);
        }
    }
}
