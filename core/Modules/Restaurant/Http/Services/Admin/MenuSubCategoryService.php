<?php

namespace Modules\Restaurant\Http\Services\Admin;


use Illuminate\Support\Str;
use Modules\Restaurant\Entities\MenuSubCategory;

class MenuSubCategoryService
{
    public static function createOrUpdate($request,$menuSubCategory = null){

        if(is_null($menuSubCategory))
        {
            $menuSubCategory = new MenuSubCategory();
        }

        $data = $request->all();
        $menuSubCategory->setTranslation('name',$request->lang,$data['name']);
        $menuSubCategory->setTranslation('description',$request->lang,$data['description']);
        $menuSubCategory->slug = Str::slug($data['name']);
        $menuSubCategory->status = $data['status'];
        $menuSubCategory->image_id = $data['image_id'];
        $menuSubCategory->menu_category_id = $data['category_id'];
        $menuSubCategory->save();

        return $menuSubCategory;
    }

}
