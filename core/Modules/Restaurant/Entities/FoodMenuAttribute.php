<?php

namespace Modules\Restaurant\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FoodMenuAttribute extends Model
{
    use HasFactory;

    protected $fillable = ['term','value','extra_price','food_menu_id','menu_attribute_id'];

    protected $table = 'food_menu_attributes';

    protected static function newFactory()
    {
        return \Modules\Restaurant\Database\factories\FoodMenuAttributeFactory::new();
    }
}
