<div class="image-product-wrapper">
    @if(isset($foodMenu))
        <x-fields.media-upload :id="$foodMenu->image_id" :title="__('Feature Image')" :name="'image_id'"
                               :dimentions="'550x550'"/>

        @php
            if (!is_null($foodMenu->gallery_images))
            {
                $image_arr = optional($foodMenu->gallery_images)->toArray();
            $gallery = '';
            foreach ($image_arr as $key => $arr)
                {
                    $gallery .= $arr['id'];
                    if ($key != count($image_arr)-1)
                        {
                            $gallery .= '|';
                        }
                }
            }
        @endphp
        <x-landlord-others.edit-media-upload-gallery :label="'Image Gallery'" :name="'menu_gallery'"
                                                     :value="$gallery ?? ''" :size="'550x550'" />
    @else
        <x-fields.media-upload :title="__('Feature Image')" :name="'image_id'" :dimentions="'550x550'"/>
        <x-landlord-others.edit-media-upload-gallery :label="'Image Gallery'" :name="'menu_gallery'"
                                                     :value="$gallery ?? ''" :size="'550x550'" />
    @endif
</div>
