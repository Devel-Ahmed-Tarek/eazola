<?php

namespace Modules\Attributes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Attributes\Http\Requests\Concerns\NormalizesAttributeRequest;

class StoreCategoryRequest extends FormRequest
{
    use NormalizesAttributeRequest;

    public function rules()
    {
        return [
            'name' => 'required|string|max:191|unique:categories',
            'slug' => 'nullable|string|max:191',
            'description' => 'nullable|string|max:5000',
            'status_id' => 'required|integer',
            'image_id' => 'nullable|integer',
        ];
    }

    public function authorize()
    {
        return true;
    }
}
