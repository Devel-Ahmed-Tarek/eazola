<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiCustomPageBlueprint extends Model
{
    protected $fillable = [
        'page_id',
        'mode',
        'entity_name',
        'schema_json',
        'data_bindings',
        'required_routes',
        'sanitized_html',
        'ai_prompt',
    ];

    protected $casts = [
        'schema_json' => 'array',
        'data_bindings' => 'array',
        'required_routes' => 'array',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class, 'page_id');
    }
}
