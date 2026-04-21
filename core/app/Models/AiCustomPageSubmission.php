<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiCustomPageSubmission extends Model
{
    protected $fillable = [
        'page_id',
        'user_id',
        'entity_name',
        'payload_json',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'payload_json' => 'array',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class, 'page_id');
    }
}
