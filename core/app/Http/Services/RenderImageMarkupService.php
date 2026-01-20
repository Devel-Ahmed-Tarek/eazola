<?php

namespace App\Http\Services;

/**
 * @returns RenderImageMarkupService
 */

class RenderImageMarkupService
{
    protected $parent_markup;
    protected $child_markup;
    protected $grandchild_markup;
    protected $image;

    public function getParent($parent_image, $parent_class = '', $parent_style = '')
    {
        $this->image = $parent_image;
        $this->parent_markup = '<div class="'.$parent_class.' blurred-img"'.$this->render_preloaded_image($this->image, $parent_style).'>';
        return $this;
    }

    public function getChild($child_ref = '#', $child_class = '', $child_id = '')
    {
        $this->child_markup = !empty($child_ref) ? '<a class="'.$child_class.'" id="'.$child_id.'" href="'.$child_ref.'">' : '';
        return $this;
    }

    public function getGrandChild($grandchild_class = '', $size = 'full', $default = false, $is_lazy = true)
    {
        $this->grandchild_markup = render_image_markup_by_attachment_id($this->image, $grandchild_class, $size, $default, $is_lazy);
        return $this;
    }

    public function renderAll()
    {
        $output = $this->parent_markup;
        $output .= $this->child_markup;
        $output .= $this->grandchild_markup;
        $output .= '</a>';
        $output .= '</div>';

        return $output;
    }

    public function render()
    {
        $output = $this->parent_markup;
        $output .= $this->grandchild_markup;
        $output .= '</div>';

        return $output;
    }

    private function render_preloaded_image($image, $styles = '')
    {
        $image_data = get_attachment_image_by_id($image, 'tiny');
        $image_data = !empty($image_data) ? $image_data['img_url'] : '';
        return 'style="background-image: url('.$image_data.');'.$styles.'"';
    }
}
