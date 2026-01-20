@php
    if(!isset($foodMenu)){
        $foodMenu = null;
    }
@endphp

<div class="general-info-wrapper">
    <h4 class="dashboard-common-title-two"> {{ __("General Information") }} </h4>
    <div class="general-info-form mt-0 mt-lg-4">
        <form action="#">
            <div class="dashboard-input mt-4">
                <label class="dashboard-label color-light mb-2"> {{ __("Name") }} </label>
                <input type="text" class="form--control radius-10" id="menu-name" value="{{ $foodMenu?$foodMenu->getTranslation('name',$defaultLang) : "" }}" name="name" placeholder="{{ __("Write menu Name...") }}">
            </div>
            <div class="dashboard-input mt-4">
                <label class="dashboard-label color-light mb-2"> {{ __("Sku") }} </label>
                <input type="text" class="form--control radius-10" id="menu-name" value="{{ $foodMenu?->sku ?? "" }}" name="sku" placeholder="{{ __("Write menu Sku...") }}">
            </div>
            <div class="dashboard-input mt-4">
                <label class="dashboard-label color-light mb-2"> {{ __("Title") }} </label>
                <input type="text" class="form--control radius-10" id="menu-name" value="{{ $foodMenu?$foodMenu->getTranslation('title',$defaultLang) : "" }}" name="title" placeholder="{{ __("Write menu title...") }}">
            </div>
            <div class="dashboard-input mt-4">
                <label class="dashboard-label color-light mb-2"> {{ __("Description") }} </label>
                <textarea class="form--control summernote radius-10" name="description" placeholder="{{ __("Type Description") }}">{!! $foodMenu?->getTranslation('description',$defaultLang) ?? "" !!}</textarea>
            </div>
        </form>
    </div>
</div>
