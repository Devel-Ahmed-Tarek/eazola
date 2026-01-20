<?php

namespace Modules\Restaurant\Http\Services\Admin;


use Illuminate\Support\Str;
use Modules\Restaurant\Entities\MenuTax;

class MenuTaxService
{
    public static function createOrUpdate($data,$menuTax = null){

        if(is_null($menuTax))
        {
            $menuTax = new MenuTax();
        }
        $menuTax->setTranslation('name',$data['lang'],$data['name']);
        $menuTax->slug = Str::slug($data['name']);
        $menuTax->tax = $data['tax'];
        $menuTax->status = $data['status'];
        $menuTax->setTranslation('description',$data['lang'],$data['description']);
        $menuTax->save();

        return $menuTax;
    }

}
