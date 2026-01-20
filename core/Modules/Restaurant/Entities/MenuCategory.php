<?php

namespace Modules\Restaurant\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuCategory extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;



    protected $fillable = [];

    protected $translatable = ['name','description'];

    protected static function newFactory()
    {
        return \Modules\Restaurant\Database\factories\MenuCategoryFactory::new();
    }

    public function food_menus()
    {
     return $this->hasMany(FoodMenu::class);
    }
}
