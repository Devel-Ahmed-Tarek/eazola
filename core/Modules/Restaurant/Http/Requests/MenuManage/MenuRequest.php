<?php

namespace Modules\Restaurant\Http\Requests\MenuManage;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "name" => "required",
            "title" => "required",
            "description" => "required",
            "lang" => "nullable",
            "price" => "nullable",
            "sale_price" => "required",
            "sku" => ["required", ($this->id ?? null) ? Rule::unique("food_menus")->ignore($this->id,"id") :  Rule::unique("food_menus")],
            "image_id" => "required",
            "menu_gallery" => "nullable",
            "tags" => "required",
            "item_additional_price" => "nullable",
            "item_attribute_name" => "nullable",
            "item_attribute_value" => "nullable",
            "category_id" => "required",
            "sub_category" => "required",
            "general_title" => "nullable",
            "general_description" => "nullable",
            "general_image" => "nullable",
            "facebook_title" => "nullable",
            "facebook_description" => "nullable",
            "facebook_image" => "nullable",
            "twitter_title" => "nullable",
            "twitter_description" => "nullable",
            "twitter_image" => "nullable",
            "min_purchase" => "nullable",
            "max_purchase" => "nullable",
            "is_refundable" => "nullable",
            "is_orderable" => "nullable",
            "menu_tax_id" => "nullable",
            "is_inventory_warn_able" => "nullable",
            "policy_description" => "nullable"
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
