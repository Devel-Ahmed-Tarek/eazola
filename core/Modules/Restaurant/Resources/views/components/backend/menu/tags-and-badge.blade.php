@php

    if(!isset($tag)){

        $tag = null;
        $tag_name = "";
    }else{
        $tag_name_arr = $tag->pluck("tag_name")?->toArray();
        $tag_name = implode(",",$tag_name_arr ?? []);
    }

    if(!isset($foodMenu)){
        $foodMenu = null;
    }

@endphp

<div class="general-info-wrapper px-3">
    <h5 class="dashboard-common-title-two">{{ __("Menu Tags") }}</h5>
    <div class="dashboard-input mt-4">
        <label class="dashboard-label color-light mb-2"> {{ __("Tags") }} </label>
        <input type="text" name="tags" class="form--control tags_input radius-10" data-role="tagsinput" value="{{ $foodMenu?$foodMenu->getTranslation('tag',$defaultLang) : "" }}">
    </div>
</div>
