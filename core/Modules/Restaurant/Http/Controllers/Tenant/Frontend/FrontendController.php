<?php

namespace Modules\Restaurant\Http\Controllers\Tenant\Frontend;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Modules\Restaurant\Entities\FoodMenu;
use Modules\Restaurant\Entities\MenuCategory;
use Modules\Restaurant\Entities\MenuSubCategory;

class FrontendController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function menuDetails($slug)
    {
        $menu = FoodMenu::with('category','food_menu_attributes')->where('slug',$slug)->first();
        $menu_categories = MenuCategory::with('food_menus')->get();
        $menu_sub_categories = MenuSubCategory::get();
        $menu['bg_image'] = "main_bg.jpg";

        return view('restaurant::frontend.menu.menu-details',compact('menu','menu_categories','menu_sub_categories'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('restaurant::create');
    }

}
