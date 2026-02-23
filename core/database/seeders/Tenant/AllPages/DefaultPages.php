<?php

namespace Database\Seeders\Tenant\AllPages;

use App\Helpers\SeederHelpers\JsonDataModifier;
use App\Models\Page;
use App\Models\PageBuilder;
use Database\Seeders\Tenant\ModuleData\MenuSeed;

class DefaultPages
{

    /**
     * عند إنشاء موقع جديد: تنزل فقط الصفحات الديفولت للسيم (نفس منطق "اختر السيمات" + تغيير السيم).
     */
    public static function execute()
    {
            $payment_log = tenant()->payment_log()?->first() ?? [];
            $current_theme = $payment_log->theme ?? null;

            if (empty($current_theme)) {
                return;
            }

            $object = new JsonDataModifier('', 'dynamic-pages');
            $data = $object->getColumnDataForDynamicPage([
                'id',
                'title',
                'page_content',
                'slug',
                'page_builder',
                'breadcrumb',
                'status',
                'theme_slug',
                'default_for_themes',
            ], true, true);

            if (empty($data)) {
                return;
            }

            // نفس فلتر تغيير السيم: الصفحة تنزل لو معلمة كديفولت لهذا السيم أو theme_slug = السيم (للتوافق)
            $filter_data = array_filter($data, function ($item) use ($current_theme) {
                $default_for = $item['default_for_themes'] ?? null;
                if (is_array($default_for) && ! empty($default_for)) {
                    return in_array($current_theme, $default_for);
                }
                return ($item['theme_slug'] ?? null) === $current_theme;
            });

            $filter_data = array_values($filter_data);
            if (empty($filter_data)) {
                return;
            }

            // الصفحة الرئيسية: اللي theme_slug = السيم، وإلا أول صفحة
            $homepageData = null;
            foreach ($filter_data as $item) {
                if (($item['theme_slug'] ?? null) === $current_theme) {
                    $homepageData = $item;
                    break;
                }
            }
            if (! $homepageData) {
                $homepageData = $filter_data[0];
            }

            $mapped_data = array_map(function ($item) {
                unset($item['theme_slug'], $item['default_for_themes']);
                return $item;
            }, $filter_data);

            Page::insert($mapped_data);

            $homepage_id = $homepageData['id'] ?? null;
            if ($homepage_id) {
                $home_page_layout_file = $current_theme . '-layout.json';
                $layout_path = 'assets/tenant/page-layout/home-pages/' . $home_page_layout_file;
                if (file_exists($layout_path)) {
                    self::upload_layout($home_page_layout_file, $homepage_id);
                }
                try {
                    MenuSeed::execute($homepage_id);
                } catch (\Exception $e) {
                }
                update_static_option('home_page', $homepage_id);
            }
    }


    private static function upload_layout($file, $page_id)
    {
        $file_contents =  json_decode(file_get_contents('assets/tenant/page-layout/home-pages/'.$file));
        $file_contents = $file_contents->data ?? $file_contents;

        $contentArr = [];
        if (current($file_contents ?? [])->addon_page_type == 'dynamic_page')
        {
            foreach ($file_contents as $key => $content)
            {
                unset($content->id);
                $content->addon_page_id = (int)trim($page_id);
                $content->created_at = now();
                $content->updated_at = now();

                foreach ($content as $key2 => $con)
                {
                    $contentArr[$key][$key2] = $con;
                }
            }
            try {

                $page = Page::find($page_id);

                if($page){
                    $page->update(['page_builder' => 1]);
                }
                PageBuilder::where('addon_page_id', $page_id)->delete();
                PageBuilder::insert($contentArr);
            }catch (\Exception $e){

            }



        } else {

            try {
                Page::findOrFail($page_id)->update([
                    'page_builder' => 0,
                    'page_content' => current($file_contents)->text
                ]);
            }catch (\Exception $e){


            }

        }
    }
}
