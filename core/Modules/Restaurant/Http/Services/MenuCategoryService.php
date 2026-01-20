<?php

namespace Modules\Restaurant\Http\Services;


use Illuminate\Support\Str;
use Modules\Restaurant\Entities\MenuCategory;

class MenuCategoryService
{
    public static function createOrUpdate($request,$menuCategory = null){

        if(is_null($menuCategory))
        {
            $menuCategory = new MenuCategory();
        }
        $data = $request->all();


        $menuCategory->setTranslation('name',$request->lang,$data['name']);
        $menuCategory->slug = Str::slug($data['name']);;
        $menuCategory->setTranslation('description',$request->lang,$data['description']);
        $menuCategory->status = $data['status'];
        $menuCategory->image_id = $data['image_id'];
        $menuCategory->save();

        return $menuCategory;
    }

}
