<?php

namespace Modules\Restaurant\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FoodMenuGallery extends Model
{
    use HasFactory;

    protected $fillable = ['food_menu_id','image_id'];
    protected $table = 'food_menu_galleries';

    protected static function newFactory()
    {
        return \Modules\Restaurant\Database\factories\FoodMenuGalleryFactory::new();
    }
}
