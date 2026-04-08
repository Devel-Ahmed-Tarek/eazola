<?php

namespace Modules\Knowledgebase\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KnowledgebaseRequest extends FormRequest
{

    public function rules() : array
    {
        return [
            'category_id' => 'required|string',
            'title' => 'required|string',
            'slug' => 'nullable|string',
            'description' => 'required|string',
            'status' => 'nullable|string',
            'image' => 'nullable|string|max:191',
            'ai_bulk_translations_json' => 'nullable|string|max:5000000',
        ];
    }


    public function messages() : array
    {
        return [
            'category_id.required' => 'Category field is required'
        ];
    }

    public function authorize()
    {
        return true;
    }
}
