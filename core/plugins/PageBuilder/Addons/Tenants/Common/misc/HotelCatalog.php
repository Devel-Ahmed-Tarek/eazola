<?php

namespace Plugins\PageBuilder\Addons\Tenants\Common\misc;

use App\Facades\GlobalLanguage;
use App\Helpers\SanitizeInput;
use Modules\HotelBooking\Entities\Hotel;
use Modules\HotelBooking\Entities\RoomType;
use Modules\HotelBooking\Entities\Room;
use Plugins\PageBuilder\Fields\Image;
use Plugins\PageBuilder\Fields\Number;
use Plugins\PageBuilder\Fields\Select;
use Plugins\PageBuilder\Fields\Switcher;
use Plugins\PageBuilder\Fields\Text;
use Plugins\PageBuilder\Fields\Textarea;
use Plugins\PageBuilder\PageBuilderBase;

class HotelCatalog extends PageBuilderBase
{
    public function preview_image()
    {
        return 'Tenant/Common/hotel-catalog.png';
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
                'hierarchical' => __('Hierarchical (Hotel → Room Type → Rooms)'),
                'hotels_only' => __('Hotels Only'),
                'rooms_grid' => __('Rooms Grid (Flat)'),
                'featured_rooms' => __('Featured Rooms Only'),
            ],
            'value' => $widget_saved_values['display_mode'] ?? 'hierarchical',
            'info' => __('Choose how to display the rooms')
        ]);

        // Hotel filter
        $hotels = Hotel::orderBy('sort_order', 'asc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => $item->getTranslation('name', GlobalLanguage::default_slug())];
            })->toArray();

        $output .= Select::get([
            'name' => 'filter_hotel',
            'label' => __('Filter by Hotel'),
            'placeholder' => __('All Hotels'),
            'options' => ['' => __('All Hotels')] + $hotels,
            'value' => $widget_saved_values['filter_hotel'] ?? '',
            'info' => __('Leave empty to show all hotels')
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
            'name' => 'items_per_hotel',
            'label' => __('Max Rooms Per Hotel'),
            'value' => $widget_saved_values['items_per_hotel'] ?? 6,
            'info' => __('Maximum number of rooms to show per hotel (0 = all)'),
        ]);

        $output .= Switcher::get([
            'name' => 'show_prices',
            'label' => __('Show Prices'),
            'value' => $widget_saved_values['show_prices'] ?? 'on',
        ]);

        $output .= Switcher::get([
            'name' => 'show_guests',
            'label' => __('Show Max Guests'),
            'value' => $widget_saved_values['show_guests'] ?? 'on',
        ]);

        $output .= Switcher::get([
            'name' => 'show_images',
            'label' => __('Show Room Images'),
            'value' => $widget_saved_values['show_images'] ?? 'on',
        ]);

        $output .= Switcher::get([
            'name' => 'show_hotel_icons',
            'label' => __('Show Hotel Icons'),
            'value' => $widget_saved_values['show_hotel_icons'] ?? 'on',
        ]);

        $output .= Switcher::get([
            'name' => 'show_amenities',
            'label' => __('Show Room Amenities'),
            'value' => $widget_saved_values['show_amenities'] ?? 'on',
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
        $filter_hotel = $this->setting_item('filter_hotel');
        $layout_style = $this->setting_item('layout_style') ?? 'accordion';
        $items_per_hotel = (int) ($this->setting_item('items_per_hotel') ?? 6);
        $columns = $this->setting_item('columns') ?? '3';
        
        $show_prices = $this->setting_item('show_prices') === 'on';
        $show_guests = $this->setting_item('show_guests') === 'on';
        $show_images = $this->setting_item('show_images') === 'on';
        $show_hotel_icons = $this->setting_item('show_hotel_icons') === 'on';
        $show_amenities = $this->setting_item('show_amenities') === 'on';
        
        $section_bg_image = $this->setting_item('section_bg_image');
        $padding_top = SanitizeInput::esc_html($this->setting_item('padding_top'));
        $padding_bottom = SanitizeInput::esc_html($this->setting_item('padding_bottom'));

        // Build hotels query
        $hotelsQuery = Hotel::orderBy('sort_order', 'asc');
        
        if (!empty($filter_hotel)) {
            $hotelsQuery->where('id', $filter_hotel);
        }

        // Check if status column exists, if so filter by it
        try {
            $hotelsQuery->where('status', '1');
        } catch (\Exception $e) {
            // Status column doesn't exist yet, skip the filter
        }

        $hotels = $hotelsQuery->with(['room_type' => function($query) {
            try {
                $query->where('status', '1')->orderBy('sort_order', 'asc');
            } catch (\Exception $e) {
                $query->orderBy('id', 'asc');
            }
        }])->get();

        // Load rooms for each hotel/room_type
        foreach ($hotels as $hotel) {
            foreach ($hotel->room_type as $roomType) {
                $roomsQuery = Room::where('room_type_id', $roomType->id);
                
                try {
                    $roomsQuery->where('status', '1');
                } catch (\Exception $e) {
                    // Status column doesn't exist yet
                }
                
                try {
                    $roomsQuery->orderBy('sort_order', 'asc');
                } catch (\Exception $e) {
                    $roomsQuery->orderBy('id', 'asc');
                }
                
                if ($items_per_hotel > 0) {
                    $roomsQuery->limit($items_per_hotel);
                }
                $roomType->rooms_list = $roomsQuery->get();
            }
        }

        // For featured rooms mode
        $featured_rooms = [];
        if ($display_mode === 'featured_rooms') {
            $featuredQuery = Room::orderBy('id', 'desc');
            
            try {
                $featuredQuery->where('status', '1')->where('is_featured', 'on');
            } catch (\Exception $e) {
                // Columns don't exist yet
            }
            
            if ($items_per_hotel > 0) {
                $featuredQuery->limit($items_per_hotel);
            }
            $featured_rooms = $featuredQuery->get();
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
            'show_guests' => $show_guests,
            'show_images' => $show_images,
            'show_hotel_icons' => $show_hotel_icons,
            'show_amenities' => $show_amenities,
            'hotels' => $hotels,
            'featured_rooms' => $featured_rooms,
            'section_bg_image' => $section_bg_image,
            'padding_top' => $padding_top,
            'padding_bottom' => $padding_bottom,
            'current_lang' => $current_lang,
        ];

        return self::renderView('tenant.Common.hotel-catalog', $data);
    }

    public function enable(): bool
    {
        return (bool) !is_null(tenant());
    }

    public function addon_title()
    {
        return __('Hotel & Rooms Catalog');
    }
}
