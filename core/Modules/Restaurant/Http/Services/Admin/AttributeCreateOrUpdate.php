<?php

namespace Modules\Restaurant\Http\Services\Admin;
use Modules\Restaurant\Entities\MenuAttribute;


class AttributeCreateOrUpdate
{
    public static function createOrUpdate($request,$menuAttribute = null){

        if(is_null($menuAttribute))
        {
            $menuAttribute = new MenuAttribute();
        }
        $menuAttribute->title = $request['title'];
        $menuAttribute->terms = json_encode($request['terms']);
        $menuAttribute->save();

        return $menuAttribute;
    }

}
