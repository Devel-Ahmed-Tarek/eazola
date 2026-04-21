<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use HasFactory,HasTranslations;
    protected $fillable = [
        'title',
        'page_content',
        'slug',
        'visibility',
        'page_builder',
        'status',
        'breadcrumb',
        'navbar_variant',
        'footer_variant',
        'show_header',
        'show_footer',
        'show_social_header',
    ];
    public $translatable = ['title','page_content'];

    public function metainfo(){
        return $this->morphOne(MetaInfo::class,'metainfoable');
    }

    public function aiCustomBlueprint()
    {
        return $this->hasOne(AiCustomPageBlueprint::class, 'page_id');
    }

    protected $casts = [
        'visibility' => 'integer',
        'page_builder' => 'integer',
        'breadcrumb' => 'integer',
        'status' => 'integer',
        'show_header' => 'boolean',
        'show_footer' => 'boolean',
        'show_social_header' => 'boolean',
    ];
}
