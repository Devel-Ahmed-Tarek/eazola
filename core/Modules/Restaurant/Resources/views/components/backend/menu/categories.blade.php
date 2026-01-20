@php
    if(!isset($selectedcat)){
        $selectedcat = null;
    }

    if(!isset($selectedSubCat)){
        $selectedSubCat = null;
    }

    if(!isset($foodMenu)){
        $foodMenu = null;
    }
    if(!isset($subCategories)){
        $subCategories = [];
    }


    $categories = !isset($categories) ? [] : $categories;
    $sub_categories = !isset($subCategories) ? [] : $subCategories;
    $child_categories = !isset($childCategories) ? [] : $childCategories;
@endphp

<div class="dashboard-attr-add-wrapper">
    <h4 class="dashboard-common-title-two"> {{ __("Categories") }} </h4>
    <div class="dashboard-input mt-4">
        <label class="dashboard-label color-light mb-2">{{ __("Category") }}</label>
        <div class="nice-select-two">
            <select name="category_id" id="category" class="form-control">
                <option value="">{{ __("Select Category") }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{  @$foodMenu->menu_category_id === $category->id ? "selected" : "" }}>{{ $category->getTranslation('name',$defaultLang) }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="dashboard-input mt-4">
        <label class="dashboard-label color-light mb-2">{{ __("Sub Category") }}</label>
        <div class="nice-select-two">
            <select class="form-control" name="sub_category" id="sub_category">
                <option value="">{{ __("Select Sub Category") }}</option>
                @foreach($subCategories as $item)
                    <option value="{{ $item->id }}" {{ $item->id === $foodMenu->menu_sub_category_id ? "selected" : "" }}>{{ $item->getTranslation('name',$defaultLang) }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
