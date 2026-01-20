<?php

namespace Modules\Restaurant\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class MenuSubCategory extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [];
    protected $translatable = ['name','description'];

    protected static function newFactory()
    {
        return \Modules\Restaurant\Database\factories\MenuSubCategoryFactory::new();
    }

    public function menu_category(){
        return $this->belongsTo(MenuCategory::class);
    }
}
