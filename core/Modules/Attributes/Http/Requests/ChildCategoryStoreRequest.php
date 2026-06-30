<?php

namespace Modules\Attributes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Attributes\Http\Requests\Concerns\NormalizesAttributeRequest;

class ChildCategoryStoreRequest extends FormRequest
{
    use NormalizesAttributeRequest;

    public function rules()
    {
        return [
            'name' => 'required|string|max:191|unique:child_categories',
            'slug' => 'nullable|string|max:191',
            'description' => 'nullable|string|max:5000',
            'status_id' => 'required|integer',
            'image_id' => 'nullable|integer',
            'category_id' => 'required|integer|exists:categories,id',
            'sub_category_id' => 'required|integer|exists:sub_categories,id',
        ];
    }

    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'category_id.required' => __('Please select a category.'),
            'category_id.integer' => __('Please select a valid category.'),
            'category_id.exists' => __('Please select a valid category.'),
            'sub_category_id.required' => __('Please select a sub category.'),
            'sub_category_id.integer' => __('Please select a valid sub category.'),
            'sub_category_id.exists' => __('Please select a valid sub category.'),
        ];
    }
}
