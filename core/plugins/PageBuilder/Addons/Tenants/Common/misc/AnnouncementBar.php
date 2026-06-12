<?php

namespace Plugins\PageBuilder\Addons\Tenants\Common\misc;

use App\Helpers\SanitizeInput;
use Plugins\PageBuilder\Fields\ColorPicker;
use Plugins\PageBuilder\Fields\Repeater;
use Plugins\PageBuilder\Fields\Select;
use Plugins\PageBuilder\Fields\Switcher;
use Plugins\PageBuilder\Fields\Text;
use Plugins\PageBuilder\Helpers\RepeaterField;
use Plugins\PageBuilder\PageBuilderBase;

class AnnouncementBar extends PageBuilderBase
{
    public function preview_image()
    {
        return 'Tenant/Common/announcement-bar.png';
    }

    public function admin_render()
    {
        $output = $this->admin_form_before();
        $output .= $this->admin_form_start();
        $output .= $this->default_fields();

        $widget_saved_values = $this->get_settings();

        $output .= Repeater::get([
            'settings' => $widget_saved_values,
            'multi_lang' => true,
            'id' => 'announcement_bar_repeater',
            'fields' => [
                [
                    'type' => RepeaterField::TEXT,
                    'name' => 'repeater_text',
                    'label' => __('Text'),
                ],
                [
                    'type' => RepeaterField::TEXT,
                    'name' => 'repeater_url',
                    'label' => __('Link URL (optional)'),
                ],
            ],
        ]);

        $output .= Text::get([
            'name' => 'separator',
            'label' => __('Separator'),
            'value' => $widget_saved_values['separator'] ?? '|',
            'info' => __('Character shown between messages, e.g. |'),
        ]);

        $output .= Switcher::get([
            'name' => 'enable_marquee',
            'label' => __('Enable Scrolling Animation'),
            'value' => $widget_saved_values['enable_marquee'] ?? null,
        ]);

        $output .= Select::get([
            'name' => 'text_align',
            'label' => __('Text Alignment'),
            'options' => [
                'center' => __('Center'),
                'start' => __('Start'),
                'end' => __('End'),
            ],
            'value' => $widget_saved_values['text_align'] ?? 'center',
        ]);

        $output .= ColorPicker::get([
            'name' => 'bg_color',
            'label' => __('Background Color'),
            'value' => $widget_saved_values['bg_color'] ?? '#F5F0E8',
        ]);

        $output .= ColorPicker::get([
            'name' => 'text_color',
            'label' => __('Text Color'),
            'value' => $widget_saved_values['text_color'] ?? '#1a1a1a',
        ]);

        $output .= $this->padding_fields($widget_saved_values);
        $output .= $this->admin_form_submit_button();
        $output .= $this->admin_form_end();
        $output .= $this->admin_form_after();

        return $output;
    }

    public function frontend_render()
    {
        $user_lang = get_user_lang();
        $repeater_data = $this->setting_item('announcement_bar_repeater') ?? [];

        $texts = $repeater_data['repeater_text_' . $user_lang] ?? [];
        $urls = $repeater_data['repeater_url_' . $user_lang] ?? [];

        $items = [];
        foreach ($texts as $key => $text) {
            $text = trim((string) $text);
            if ($text === '') {
                continue;
            }

            $items[] = [
                'text' => SanitizeInput::esc_html($text),
                'url' => SanitizeInput::esc_url($urls[$key] ?? ''),
            ];
        }

        $data = [
            'items' => $items,
            'separator' => SanitizeInput::esc_html($this->setting_item('separator') ?: '|'),
            'enable_marquee' => !empty($this->setting_item('enable_marquee')),
            'text_align' => SanitizeInput::esc_html($this->setting_item('text_align') ?: 'center'),
            'bg_color' => SanitizeInput::esc_html($this->setting_item('bg_color') ?: '#F5F0E8'),
            'text_color' => SanitizeInput::esc_html($this->setting_item('text_color') ?: '#1a1a1a'),
            'padding_top' => SanitizeInput::esc_html($this->setting_item('padding_top')),
            'padding_bottom' => SanitizeInput::esc_html($this->setting_item('padding_bottom')),
            'rand_number' => $this->rand_number,
        ];

        return self::renderView('tenant.Common.misc.announcement-bar', $data);
    }

    public function enable(): bool
    {
        return (bool) !is_null(tenant());
    }

    public function addon_title()
    {
        return __('Announcement Bar');
    }
}
