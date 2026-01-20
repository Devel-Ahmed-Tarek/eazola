<?php

namespace Modules\Restaurant\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuCreateOrUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required','string','max:191', Rule::unique('sub_categories')->ignore($this->id)],
            'slug' => 'nullable|string|max:191',
            'description' => 'nullable',
            'status' => 'required|string|max:191',
            'image_id' => 'nullable|string|max:191',
            'category_id' => 'required|max:191'
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
