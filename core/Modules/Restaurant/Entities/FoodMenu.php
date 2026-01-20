<?php

namespace Modules\Restaurant\Entities;

use App\Models\MediaUploader;
use App\Models\MetaInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Translatable\HasTranslations;


class FoodMenu extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [];
    protected $translatable = ['name','title','description','policy_description','tag'];
    protected $table = 'food_menus';

    protected static function newFactory()
    {
        return \Modules\Restaurant\Database\factories\FoodMenuFactory::new();
    }

    public function food_menu_attributes(){
        return $this->hasMany(FoodMenuAttribute::class,"food_menu_id","id");
    }

    function menu_tax(){
        return $this->belongsTo(MenuTax::class,"menu_tax_id","id");
    }

    public function gallery_images() {
        return $this->hasManyThrough(MediaUploader::class, FoodMenuGallery::class,"food_menu_id","id","id","image_id");
    }

    public function metaData(): MorphOne {
        return $this->morphOne(MetaInfo::class,"metainfoable");
    }

    public function category(){
        return $this->belongsTo(MenuCategory::class,'menu_category_id','id');
    }

}
