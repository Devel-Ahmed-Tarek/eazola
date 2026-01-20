
@php
    if(!isset($allAvailableAttributes)){
        $allAvailableAttributes = [];
    }
    if(!isset($detail)){
        $detail = null;
    }
    if(!isset($allAttributeValues)){
        $allAttributeValues = [];
    }
@endphp

<div class="inventory_item shadow-sm rounded" @if(isset($key)) data-id="{{ $key }}" @endif>
    @if(isset($inventoryDetail) && !is_null($inventoryDetail))
        <input type="hidden" name="inventory_details_id[]" value="{{ $inventoryDetail->id }}">
    @endif
    <div class="row">
        <div class="col">
            <div class="form-row  row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-3 row-cols-xxl-3">

                <div class="col">
                    <div class="form-group">
                        <label for="item_size">{{ __('Attribute name') }}</label>
                        <select name="item_attribute_name[]" id="item_size" class="form-control item_attribute_name">
                            <option >{{ __('Select Attribute Name') }}</option>
                            @foreach($allAvailableAttributes as $attribute)
                                <option value="{{ $attribute->id }}"
                                        @if(isset($detail) && $detail->menu_attribute_id == $attribute->id) selected @endif
                                        data-terms="{{ $attribute->terms }}">{{ $attribute->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col">
                    <div class="form-group">
                        <label for="item_size">{{ __('Attribute value') }}</label>
                        <select name="item_attribute_value[]" class="form-control item_attribute_value">
                            @foreach($allAttributeValues as $value)
                                <option value="{{$value }}"
                                        @if(isset($detail) && $detail->value == $value) selected @endif
                                >{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col">
                    <div class="form-group">
                        <label for="item_additional_price">{{ __('Additional Price') }}</label>
                        <input type="number" step="0.01" name="item_additional_price[]" id="item_additional_price"
                               class="form-control form--control" min="0" placeholder="{{ __('Additional price') }}"
                               value="{{ $detail?->additional_price ?? 0 }}"
                        >
                    </div>
                </div>

            </div>
            <div class="item_selected_attributes">
                @if(isset($detail) && !is_null($detail) && !is_null($detail->attribute))
                    @foreach($detail->attribute as $attribute)
                        <div class="form-row row row-cols-1 row-cols-sm-2 row-cols-lg-3">
                            <div class="col">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="item_attribute_name[{{ $key }}][]" value="{{ $attribute->attribute_name }}" readonly />
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="item_attribute_value[{{ $key }}][]" value="{{ $attribute->attribute_value }}" readonly />
                                </div>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-danger remove_details_attribute" data-id="{{ $attribute->id }}"> x </button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

        </div>
        <div class="col-auto">
            <div class="item_repeater_add_remove">
                <div class="repeater_button">
                    <button type="button" class="btn btn-success btn-xs add"><i class="las la-plus"></i></button>
                </div>
                @if(!isset($isFirst) || !$isFirst)
                    <div class="repeater_button mt-2">
                        <button type="button" class="btn btn-danger btn-xs remove"> <i class="las la-trash-alt"></i> </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>


@if(isset($not_needed))
<div class="variant_variant_info_repeater">
    <div class="form-row">
        <div class="col">
            <div class="form-group">
                <label for="variant_color">{{ __('Color') }}</label>
                @isset($variantId)
                    <input type="hidden" class="variant_id" name="variant_id[]" value="{{ $variantId }}">
                @endisset
                <select class="form-control" name="variant_color[]" id="variant_color">
                    <option value="">{{ __('Select Color') }}</option>
                    @foreach($colors as $color)
                        <option value="{{ $color->id }}"
                                @if(isset($selectedColor) && $selectedColor->id == $color->id) selected @endif>{{ $color->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col">
            <div class="form-group">
                <label for="variant_size">{{ __('Size') }}</label>
                <select class="form-control" name="variant_size[]" id="variant_size">
                    <option value="">{{ __('Select Size') }}</option>
                    @foreach($sizes as $size)
                        <option value="{{ $size->id }}"
                                @if(isset($selectedSize) && $selectedSize->id == $size->id) selected @endif>{{ $size->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col">
            <div class="form-group">
                <label for="variant_stock_count">{{ __('Quantity') }}</label>
                <input type="number" name="variant_stock_count[]" id="variant_stock_count" class="form-control"
                       step="0.01" @if(isset($quantity)) value="{{ $quantity }}" @endif>
            </div>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-sm btn-success add_variant_info_btn"> <i class="las la-plus"></i>{{__('add')}}</button>
           @if($loop != 1)
                <button type="button"
                        class="btn btn-sm btn-danger remove_this_variant_info_btn @if(isset($variantId)) remove_variant @endif"
                        @if(isset($isFirst) && $isFirst) readonly @endif > <i class="las la-trash-alt"></i>
                </button>
           @endif
        </div>
    </div>
</div>
@endif
