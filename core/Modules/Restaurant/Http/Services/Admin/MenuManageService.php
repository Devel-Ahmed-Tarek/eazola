<?php

namespace Modules\Restaurant\Http\Services\Admin;

use App\Helpers\SanitizeInput;
use Illuminate\Support\Str;
use Modules\Campaign\Entities\CampaignSoldProduct;
use Modules\Restaurant\Entities\FoodMenu;
use Modules\Restaurant\Entities\FoodMenuAttribute;
use Modules\Restaurant\Entities\FoodMenuGallery;
use Modules\Restaurant\Entities\MenuAttribute;
use Modules\Restaurant\Http\Traits\MenuGlobalTrait;

class MenuManageService
{
    use MenuGlobalTrait;

    public static function createOrUpdate($request, $foodMenu= null)
    {
        try {
            if(is_null($foodMenu))
            {
                $foodMenu = new FoodMenu();
            }
            $foodMenu->setTranslation('name',$request['lang'], SanitizeInput::esc_html($request['name']));
            $foodMenu->setTranslation('title',$request['lang'], SanitizeInput::esc_html($request['title']));
            $foodMenu->setTranslation('description',$request['lang'], SanitizeInput::esc_html($request['description']));
            $foodMenu->setTranslation('policy_description',$request['lang'], SanitizeInput::esc_html($request['policy_description']));
            $foodMenu->setTranslation('tag',$request['lang'], SanitizeInput::esc_html($request['tags']));
            $foodMenu->slug = Str::slug($request['name']);
            $foodMenu->image_id = $request['image_id'];
            $foodMenu->sku = $request['sku'];
            $foodMenu->status = $request['status'] ?? 1;
            $foodMenu->regular_price = $request['price'];
            $foodMenu->sale_price = $request['sale_price'];
            $foodMenu->min_purchase = $request['min_purchase'];
            $foodMenu->max_purchase = $request['max_purchase'];
            $foodMenu->is_orderable = @$request['is_orderable'] == "on" ? 1 : 0;
            $foodMenu->menu_category_id = $request['category_id'];
            $foodMenu->menu_tax_id = $request['menu_tax_id'];
            $foodMenu->menu_sub_category_id = $request['sub_category'];

            $foodMenu->save();

            $foodMenu->metaData()->updateOrCreate(["metainfoable_id" => $foodMenu->id],self::prepareMetaData($request));
            if($request['menu_gallery'])
            {
               $menu_gallery =  FoodMenuGallery::where("food_menu_id", $foodMenu->id)->delete();
               $data = self::prepareProductGalleryData($request, $foodMenu->id);
               $menu_gallery_insert = FoodMenuGallery::insert($data);
            }

            if(@$request['item_attribute_name'])
            {

            if(count($request['item_attribute_name']) > 0)
            {
                if($foodMenu->food_menu_attributes)
                {
                    $FoodMenuAttribute =  FoodMenuAttribute::where("food_menu_id", $foodMenu->id)->delete();
                }
                for($i = 0; $i < count($request['item_attribute_name']);$i++)
                {
                    $menuAttribute = MenuAttribute::where('id',$request['item_attribute_name'][$i])->first();

                    if(!empty($menuAttribute)){
                        $data = [
                            'term'=>@$menuAttribute->title,
                            'value'=>@$request['item_attribute_value'][$i],
                            'extra_price'=>@$request['item_additional_price'][$i],
                            'menu_attribute_id'=>@$request['item_attribute_name'][$i],
                            'food_menu_id'=>@$foodMenu->id,
                        ];

                        FoodMenuAttribute::create($data);
                    }

                }
            }
            }
        }catch(\Exception $ex)
        {
            dd($ex->getMessage());
        }

        return $foodMenu;
    }

    public static  function prepareProductGalleryData($data, $id): array
    {
        // explode string to array
        $arr = [];
        $galleries = self::separateStringToArray($data["menu_gallery"], "|");

        foreach($galleries as $image){
            $arr[] = [
                "food_menu_id" => $id,
                "image_id" => $image
            ];
        }

        return $arr;
    }

    public static function separateStringToArray(string | null $string,string $separator = " , "): array|bool
    {
        if(empty($string)) return [];
        return explode($separator, $string);
    }


    public static function preparedAttribute($attribute)
    {
       return $attribute->delete();
    }

    public function store($data)
    {
        /// store product
        return $this->menuStore($data);
    }

    public function update($data, $id){
        return $this->productUpdate($data, $id);
    }

    public function get_edit_product($id){
        return $this->get_product("edit",$id);
    }

    public function clone($id){
        return $this->productClone($id);
    }

    public function delete(int $id)
    {
        CampaignSoldProduct::where('product_id', $id)->delete();
        return $this->destroy($id);
    }

    public function bulk_delete_action(array $ids)
    {
        CampaignSoldProduct::whereIn('product_id', $ids)->delete();
        return $this->bulk_delete($ids);
    }

    public function trash_delete(int $id)
    {
        return $this->trash_destroy($id);
    }

    public function trash_bulk_delete_action(array $ids)
    {
        return $this->trash_bulk_delete($ids);
    }

    public static function productSearch($request): array
    {
        $route = 'tenant.admin';
        return (new self)->search($request, $route);
    }

    public static function prepareMetaData($data)
    {
        return [
            'title' => $data["general_title"] ?? '',
            'description' => $data["general_description"] ?? '',
            'fb_title' => $data["facebook_title"] ?? '',
            'fb_description' => $data["facebook_description"] ?? '',
            'fb_image' => $data["facebook_image"] ?? '',
            'tw_title' => $data["twitter_title"] ?? '',
            'tw_description' => $data["twitter_description"] ?? '',
            'tw_image' => $data["twitter_image"] ?? ''
        ];
    }
}
