<?php

namespace Modules\Attributes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Attributes\Http\Requests\Concerns\NormalizesAttributeRequest;

class UpdateCategoryRequest extends FormRequest
{
    use NormalizesAttributeRequest;

    public function rules()
    {
        return [
            'name' => ['required','string','max:191', Rule::unique('categories')->ignore($this->id)],
            'slug' => ['required','string','max:191', Rule::unique('categories')->ignore($this->id)],
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
