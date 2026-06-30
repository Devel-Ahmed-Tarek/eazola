<?php

namespace Modules\Attributes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use JetBrains\PhpStorm\ArrayShape;
use Modules\Attributes\Http\Requests\Concerns\NormalizesAttributeRequest;

class UpdateSubCategoryRequest extends FormRequest
{
    use NormalizesAttributeRequest;

    #[ArrayShape(["id" => "string", 'name' => "string", 'slug' => "string", 'description' => "string", 'status_id' => "string", 'image_id' => "string", 'category_id' => "string"])]
    public function rules()
    {
        return [
            "id" => "required|integer",
            'name' => ['required','string','max:191', Rule::unique('sub_categories')->ignore($this->id)],
            'slug' => 'nullable|string|max:191',
            'description' => 'nullable|string|max:5000',
            'status_id' => 'required|integer',
            'image_id' => 'nullable|integer',
            'category_id' => 'required|integer|exists:categories,id',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function messages()
    {
        return [
            'category_id.required' => __('Please select a category.'),
            'category_id.integer' => __('Please select a valid category.'),
            'category_id.exists' => __('Please select a valid category.'),
        ];
    }
}
