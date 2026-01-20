<?php

namespace Modules\Restaurant\Http\Pagebuilder\Addons\Home;

use App\Facades\GlobalLanguage;
use App\Helpers\SanitizeInput;
use Modules\Restaurant\Entities\FoodMenu;
use Modules\Restaurant\Entities\MenuCategory;
use Modules\Restaurant\Entities\MenuSubCategory;
use Plugins\PageBuilder\Fields\Image;
use Plugins\PageBuilder\Fields\Text;
use Plugins\PageBuilder\PageBuilderBase;

class HomeArea extends PageBuilderBase
{
    public function preview_image()
    {
        return 'header-area.png';
    }

    public function setAssetsFilePath()
    {
        return externalAddonImagepath('Restaurant');
    }

    public function admin_render()
    {
        $output = $this->admin_form_before();
        $output .= $this->admin_form_start();
        $output .= $this->default_fields();

        $widget_saved_values = $this->get_settings();
        $output .= $this->admin_language_tab(); //have to start language tab from here on
        $output .= $this->admin_language_tab_start();
        $all_languages = GlobalLanguage::all_languages();

        foreach ($all_languages as $key => $lang) {
            $output .= $this->admin_language_tab_content_start([
                'class' => $key == 0 ? 'tab-pane fade show active' : 'tab-pane fade',
                'id' => "nav-home-" . $lang->slug
            ]);

            $output .= Text::get([
                'name' => 'top_title_one_'.$lang->slug,
                'label' => __('Top Title One'),
                'value' => $widget_saved_values['top_title_one_'.$lang->slug] ?? null,
            ]);
            $output .= Text::get([
                'name' => 'top_title_two_'.$lang->slug,
                'label' => __('Top Title Two'),
                'value' => $widget_saved_values['top_title_two_'.$lang->slug] ?? null,
            ]);
            $output .= Text::get([
                'name' => 'middle_title_'.$lang->slug,
                'label' => __('Middle Title'),
                'value' => $widget_saved_values['middle_title_'.$lang->slug] ?? null,
            ]);

            $output .= Text::get([
                'name' => 'last_title_'.$lang->slug,
                'label' => __('Last Title'),
                'value' => $widget_saved_values['last_title_'.$lang->slug] ?? null,
            ]);

            $output .= Text::get([
                'name' => 'button_text_'.$lang->slug,
                'label' => __('Button Text'),
                'value' => $widget_saved_values['button_text_'.$lang->slug] ?? null,
            ]);

            $output .= $this->admin_language_tab_content_end();
        }
        $output .= $this->admin_language_tab_end(); //have to end language tab


        $output .= Image::get([
            'name' => 'background_image',
            'label' => __('Backgraound Image'),
            'value' => $widget_saved_values['background_image'] ?? null,
            'dimensions' => '1921×930px'
        ]);
        $output .= Image::get([
            'name' => 'right_image',
            'label' => __('Right Image'),
            'value' => $widget_saved_values['right_image'] ?? null,
            'dimensions' => '975×984px'
        ]);
        $output .= Image::get([
            'name' => 'middle_image',
            'label' => __('Middle Image'),
            'value' => $widget_saved_values['middle_image'] ?? null,
            'dimensions' => '101×115px'
        ]);
        $output .= Image::get([
            'name' => 'left_image',
            'label' => __('Left Image'),
            'value' => $widget_saved_values['left_image'] ?? null,
            'dimensions' => '101×115px'
        ]);


        // add padding option
        $output .= $this->padding_fields($widget_saved_values);
        $output .= $this->admin_form_submit_button();
        $output .= $this->admin_form_end();
        $output .= $this->admin_form_after();

        return $output;
    }

    public function frontend_render()
    {
        $current_lang = get_user_lang();

        $padding_top = SanitizeInput::esc_html($this->setting_item('padding_top'));
        $padding_bottom = SanitizeInput::esc_html($this->setting_item('padding_bottom'));
        $top_title_one = $this->setting_item('top_title_one_'.$current_lang) ?? '';
        $top_title_two = $this->setting_item('top_title_two_'.$current_lang) ?? '';
        $middle_title = $this->setting_item('middle_title_'.$current_lang) ?? '';
        $middle_title_bg_image = "gradient_title.png";
        $last_title = $this->setting_item('last_title_'.$current_lang) ?? '';
        $button_text = $this->setting_item('button_text_'.$current_lang) ?? '';
        $background_image = $this->setting_item('background_image') ?? '';
        $right_image = $this->setting_item('right_image') ?? '';
        $middle_image = $this->setting_item('middle_image') ?? '';
        $left_image = $this->setting_item('left_image') ?? '';


        $food_menus = FoodMenu::with('category','food_menu_attributes')->get();
        $menu_categories = MenuCategory::with('food_menus')->get();
        $menu_sub_categories = MenuSubCategory::get();


        $data = [
            'padding_top'=> $padding_top,
            'padding_bottom'=> $padding_bottom,
            'background_image'=> $background_image,
            'right_image'=> $right_image,
            'middle_image'=> $middle_image,
            'left_image'=> $left_image,
            'button_text'=> $button_text,
            'top_title_one'=> $top_title_one,
            'top_title_two'=> $top_title_two,
            'middle_title'=> $middle_title,
            'middle_title_bg_image'=> $middle_title_bg_image,
            'last_title'=> $last_title,
            'food_menus'=> $food_menus,
            'menu_categories'=> $menu_categories,
            'menu_sub_categories'=> $menu_sub_categories,
        ];

        return self::renderView('home-area', $data, 'Restaurant');
    }

    public function addon_title()
    {
        return __('Restaurant Home (restaurant)');
    }
}
