<?php

namespace Plugins\PageBuilder\Addons\Tenants\Common\misc;

use App\Facades\GlobalLanguage;
use App\Helpers\SanitizeInput;
use Modules\Appointment\Entities\Appointment;
use Modules\Appointment\Entities\AppointmentCategory;
use Modules\Appointment\Entities\AppointmentSubcategory;
use Plugins\PageBuilder\Fields\Image;
use Plugins\PageBuilder\Fields\Number;
use Plugins\PageBuilder\Fields\Select;
use Plugins\PageBuilder\Fields\Switcher;
use Plugins\PageBuilder\Fields\Text;
use Plugins\PageBuilder\Fields\Textarea;
use Plugins\PageBuilder\PageBuilderBase;

class AppointmentCatalog extends PageBuilderBase
{
    public function preview_image()
    {
        return 'Tenant/Common/appointment-catalog.png';
    }

    public function admin_render()
    {
        $output = $this->admin_form_before();
        $output .= $this->admin_form_start();
        $output .= $this->default_fields();

        $widget_saved_values = $this->get_settings();
        $output .= $this->admin_language_tab();
        $output .= $this->admin_language_tab_start();
        $all_languages = GlobalLanguage::all_languages();

        foreach ($all_languages as $key => $lang) {
            $output .= $this->admin_language_tab_content_start([
                'class' => $key == 0 ? 'tab-pane fade show active' : 'tab-pane fade',
                'id' => "nav-home-" . $lang->slug
            ]);
            
            $output .= Text::get([
                'name' => 'section_title_'.$lang->slug,
                'label' => __('Section Title'),
                'value' => $widget_saved_values['section_title_'.$lang->slug] ?? null,
                'info' => __('Main section heading')
            ]);
            
            $output .= Textarea::get([
                'name' => 'section_subtitle_'.$lang->slug,
                'label' => __('Section Subtitle'),
                'value' => $widget_saved_values['section_subtitle_'.$lang->slug] ?? null,
                'info' => __('Optional description below the title')
            ]);
            
            $output .= Text::get([
                'name' => 'view_all_text_'.$lang->slug,
                'label' => __('View All Button Text'),
                'value' => $widget_saved_values['view_all_text_'.$lang->slug] ?? null,
            ]);
            
            $output .= Text::get([
                'name' => 'book_now_text_'.$lang->slug,
                'label' => __('Book Now Button Text'),
                'value' => $widget_saved_values['book_now_text_'.$lang->slug] ?? null,
            ]);

            $output .= $this->admin_language_tab_content_end();
        }
        $output .= $this->admin_language_tab_end();

        // Display Mode
        $output .= Select::get([
            'name' => 'display_mode',
            'label' => __('Display Mode'),
            'options' => [
                'hierarchical' => __('Hierarchical (Category → Subcategory → Services)'),
                'categories_only' => __('Categories Only'),
                'services_grid' => __('Services Grid (Flat)'),
                'featured_services' => __('Featured Services Only'),
            ],
            'value' => $widget_saved_values['display_mode'] ?? 'hierarchical',
            'info' => __('Choose how to display the services')
        ]);

        // Category filter
        $categories = AppointmentCategory::where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => $item->getTranslation('title', GlobalLanguage::default_slug())];
            })->toArray();

        $output .= Select::get([
            'name' => 'filter_category',
            'label' => __('Filter by Category'),
            'placeholder' => __('All Categories'),
            'options' => ['' => __('All Categories')] + $categories,
            'value' => $widget_saved_values['filter_category'] ?? '',
            'info' => __('Leave empty to show all categories')
        ]);

        // Layout options
        $output .= Select::get([
            'name' => 'layout_style',
            'label' => __('Layout Style'),
            'options' => [
                'accordion' => __('Accordion Style'),
                'tabs' => __('Tabs Style'),
                'cards' => __('Cards Grid'),
                'list' => __('List View'),
            ],
            'value' => $widget_saved_values['layout_style'] ?? 'accordion',
        ]);

        $output .= Number::get([
            'name' => 'items_per_category',
            'label' => __('Max Services Per Category'),
            'value' => $widget_saved_values['items_per_category'] ?? 6,
            'info' => __('Maximum number of services to show per category (0 = all)'),
        ]);

        $output .= Switcher::get([
            'name' => 'show_prices',
            'label' => __('Show Prices'),
            'value' => $widget_saved_values['show_prices'] ?? 'on',
        ]);

        $output .= Switcher::get([
            'name' => 'show_duration',
            'label' => __('Show Duration'),
            'value' => $widget_saved_values['show_duration'] ?? 'on',
        ]);

        $output .= Switcher::get([
            'name' => 'show_images',
            'label' => __('Show Service Images'),
            'value' => $widget_saved_values['show_images'] ?? 'on',
        ]);

        $output .= Switcher::get([
            'name' => 'show_category_icons',
            'label' => __('Show Category Icons'),
            'value' => $widget_saved_values['show_category_icons'] ?? 'on',
        ]);

        $output .= Select::get([
            'name' => 'columns',
            'label' => __('Columns (Grid View)'),
            'options' => [
                '2' => __('2 Columns'),
                '3' => __('3 Columns'),
                '4' => __('4 Columns'),
            ],
            'value' => $widget_saved_values['columns'] ?? '3',
        ]);

        $output .= Image::get([
            'name' => 'section_bg_image',
            'label' => __('Section Background Image'),
            'value' => $widget_saved_values['section_bg_image'] ?? null,
        ]);

        // Padding
        $output .= $this->padding_fields($widget_saved_values);
        $output .= $this->admin_form_submit_button();
        $output .= $this->admin_form_end();
        $output .= $this->admin_form_after();

        return $output;
    }

    public function frontend_render()
    {
        $current_lang = GlobalLanguage::user_lang_slug();
        
        // Get settings
        $section_title = SanitizeInput::esc_html($this->setting_item('section_title_'.$current_lang));
        $section_subtitle = SanitizeInput::esc_html($this->setting_item('section_subtitle_'.$current_lang));
        $view_all_text = SanitizeInput::esc_html($this->setting_item('view_all_text_'.$current_lang)) ?: __('View All');
        $book_now_text = SanitizeInput::esc_html($this->setting_item('book_now_text_'.$current_lang)) ?: __('Book Now');
        
        $display_mode = $this->setting_item('display_mode') ?? 'hierarchical';
        $filter_category = $this->setting_item('filter_category');
        $layout_style = $this->setting_item('layout_style') ?? 'accordion';
        $items_per_category = (int) ($this->setting_item('items_per_category') ?? 6);
        $columns = $this->setting_item('columns') ?? '3';
        
        $show_prices = $this->setting_item('show_prices') === 'on';
        $show_duration = $this->setting_item('show_duration') === 'on';
        $show_images = $this->setting_item('show_images') === 'on';
        $show_category_icons = $this->setting_item('show_category_icons') === 'on';
        
        $section_bg_image = $this->setting_item('section_bg_image');
        $padding_top = SanitizeInput::esc_html($this->setting_item('padding_top'));
        $padding_bottom = SanitizeInput::esc_html($this->setting_item('padding_bottom'));

        // Build categories query
        $categoriesQuery = AppointmentCategory::where('status', 1)
            ->orderBy('sort_order', 'asc');
        
        if (!empty($filter_category)) {
            $categoriesQuery->where('id', $filter_category);
        }

        $categories = $categoriesQuery->with(['subcategories' => function($query) {
            $query->where('status', 1)->orderBy('sort_order', 'asc');
        }])->get();

        // Load appointments for each category/subcategory
        foreach ($categories as $category) {
            // Get appointments directly under category (without subcategory)
            $directAppointmentsQuery = Appointment::where('status', 1)
                ->where('appointment_category_id', $category->id)
                ->whereNull('appointment_subcategory_id')
                ->orderBy('sort_order', 'asc');
            
            if ($items_per_category > 0) {
                $directAppointmentsQuery->limit($items_per_category);
            }
            $category->direct_appointments = $directAppointmentsQuery->get();

            // Load appointments for each subcategory
            foreach ($category->subcategories as $subcategory) {
                $appointmentsQuery = Appointment::where('status', 1)
                    ->where('appointment_subcategory_id', $subcategory->id)
                    ->orderBy('sort_order', 'asc');
                
                if ($items_per_category > 0) {
                    $appointmentsQuery->limit($items_per_category);
                }
                $subcategory->appointments_list = $appointmentsQuery->get();
            }
        }

        // For featured services mode
        $featured_services = [];
        if ($display_mode === 'featured_services') {
            $featuredQuery = Appointment::where('status', 1)
                ->where('is_featured', 'on')
                ->orderBy('sort_order', 'asc');
            
            if ($items_per_category > 0) {
                $featuredQuery->limit($items_per_category);
            }
            $featured_services = $featuredQuery->get();
        }

        $data = [
            'section_title' => $section_title,
            'section_subtitle' => $section_subtitle,
            'view_all_text' => $view_all_text,
            'book_now_text' => $book_now_text,
            'display_mode' => $display_mode,
            'layout_style' => $layout_style,
            'columns' => $columns,
            'show_prices' => $show_prices,
            'show_duration' => $show_duration,
            'show_images' => $show_images,
            'show_category_icons' => $show_category_icons,
            'categories' => $categories,
            'featured_services' => $featured_services,
            'section_bg_image' => $section_bg_image,
            'padding_top' => $padding_top,
            'padding_bottom' => $padding_bottom,
            'current_lang' => $current_lang,
        ];

        return self::renderView('tenant.Common.appointment-catalog', $data);
    }

    public function enable(): bool
    {
        return (bool) !is_null(tenant());
    }

    public function addon_title()
    {
        return __('Appointment Services Catalog');
    }
}
