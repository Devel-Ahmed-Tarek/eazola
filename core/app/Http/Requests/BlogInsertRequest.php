<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlogInsertRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'category_id' => 'required|integer|exists:blog_categories,id',
            'blog_content' => 'required',
            'title' => 'required|string|max:191',
            'status' => 'nullable|integer|in:0,1',
            'slug' => 'nullable|string|max:191',
            'image' => 'nullable|string|max:191',
            'ai_bulk_translations_json' => 'nullable|string|max:5000000',
        ];
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
