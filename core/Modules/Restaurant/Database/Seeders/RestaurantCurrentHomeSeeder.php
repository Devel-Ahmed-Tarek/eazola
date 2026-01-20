<?php

namespace Modules\Restaurant\Database\Seeders;
use Illuminate\Database\Seeder;


class RestaurantCurrentHomeSeeder extends Seeder
{
    public static function run()
    {
        $only_path = 'assets/tenant/page-layout/dynamic-pages.json';

        if (file_exists($only_path) && !is_dir($only_path) && moduleExists("Restaurant"))
        {
            $dynamic_pages = file_get_contents($only_path);
            $all_data_decoded = json_decode($dynamic_pages);

            $isRestaurantExists = collect($all_data_decoded->data)->pluck('slug')->contains("home-page-restaurant");


            if($isRestaurantExists == false)
            {
                $curren_home_path = 'core/Modules/Restaurant/assets/page-layout/home-page/restaurant-current-home.json';

                if (file_exists($curren_home_path) && !is_dir($curren_home_path))
                {
                    $current_home_page = file_get_contents($curren_home_path);
                    $additional_content = json_decode($current_home_page);

                    if ($additional_content) {
                        // Merge additional content with existing data
                        $all_data_decoded->data[] = $additional_content;

                        // Encode all data back to JSON
                        $updated_json = json_encode($all_data_decoded);

                        // Write updated JSON content to the original file
                        file_put_contents($only_path, $updated_json);
                    }
                }
            }
        }
    }
}
