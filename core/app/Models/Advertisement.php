<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Advertisement extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'advertisements';

    protected $fillable = ['type', 'size', 'image', 'slot', 'embed_code', 'redirect_url', 'click', 'impression', 'status', 'title'];

    protected $translatable = ['title'];

    protected $casts = [
        'status' => 'integer',
    ];
}

