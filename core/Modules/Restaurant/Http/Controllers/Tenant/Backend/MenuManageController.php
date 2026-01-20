<?php

namespace Modules\Restaurant\Http\Controllers\Tenant\Backend;

use App\Facades\GlobalLanguage;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HotelBooking\Http\Services\ServicesHelpers;
use Modules\Restaurant\Entities\FoodMenu;
use Modules\Restaurant\Entities\MenuAttribute;
use Modules\Restaurant\Entities\MenuSubCategory;
use Modules\Restaurant\Entities\MenuTax;
use Modules\Restaurant\Http\Requests\MenuManage\MenuRequest;
use Modules\Restaurant\Http\Services\Admin\MenuManageService;
use Modules\Restaurant\Entities\MenuCategory;


class MenuManageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:restaurant-menu-manage-all|restaurant-menu-manage-create|restaurant-menu-manage-edit|restaurant-menu-manage-delete',['only' => 'index']);
        $this->middleware('permission:restaurant-menu-manage-create',['only' => 'create','store']);
        $this->middleware('permission:restaurant-menu-manage-edit',['only' => 'edit','update']);
        $this->middleware('permission:restaurant-menu-manage-update_status',['only' => 'update_status']);
        $this->middleware('permission:restaurant-menu-manage-delete',['only' => 'destroy']);
        $this->middleware('permission:restaurant-menu-manage-bulk_action',['only' => 'bulk_action']);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $all_menus = FoodMenu::with('food_menu_attributes')->get();
        $default_lang = $request->lang ?? GlobalLanguage::default_slug();

        return view('restaurant::backend.menu-manage.all')->with([
            'all_menus' => $all_menus,
            'default_lang' => $default_lang
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $default_lang = $request->lang ?? GlobalLanguage::default_slug();
        $data = [
            "categories" => MenuCategory::select("id", "name")->get(),
            "all_attribute" => MenuAttribute::all()->groupBy('title')->map(fn($query) => $query[0]),
            "default_lang" => $default_lang,
            "menu_taxes" => MenuTax::get(),
        ];

        return view('restaurant::backend.menu-manage.create',compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(MenuRequest $request)
    {
        $data = $request->validated();

        $data['lang'] = $request->lang;

        $menuCreate = MenuManageService::createOrUpdate($data);

        return response()->json($menuCreate ? ["success" => true] : ["success" => false]);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('restaurant::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $attribute_terms = MenuAttribute::pluck('terms');

        $termMergedArray = array_reduce($attribute_terms->toArray(), function ($carry, $item) {
            $decodedArray = json_decode($item, true);
            return array_merge($carry, $decodedArray ?: []);
        }, []);

        $uniqueAllTerms = array_unique($termMergedArray);

        $data = [
            "categories" => MenuCategory::select("id", "name")->get(),
            "sub_categories" => MenuSubCategory::select("id", "name")->get(),
            "all_attribute" => MenuAttribute::all()->groupBy('title')->map(fn($query) => $query[0]),
            "food_menu" => FoodMenu::with('food_menu_attributes','metaData')->findOrFail($id),
            "default_lang" => $request->lang ?? GlobalLanguage::default_slug(),
            "menu_taxes" => MenuTax::get(),
        ];

        $food_menu = FoodMenu::with('food_menu_attributes')->findOrFail($id);

        return view('restaurant::backend.menu-manage.edit',compact('data','food_menu','uniqueAllTerms'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(MenuRequest $request, $id)
    {
        $data = $request->validated();
        $data['lang'] = $request->lang;
        $foodMenu = FoodMenu::with('food_menu_attributes')->findOrFail($id);

        $menuUpdate = MenuManageService::createOrUpdate($data,$foodMenu);

        return response()->json($menuUpdate ? ["success" => true] : ["success" => false]);
    }

    public function orderable_status_updated(Request $request)
    {
        // make validation
        $request->validate([
            "menu_id" => "required",
            "orderable_status" => "required"
        ]);

        // update payment status information
        $bool = FoodMenu::where("id",$request->menu_id)->update(["is_orderable" => $request->orderable_status]);

        return back()->with(ServicesHelpers::send_response($bool,"update"));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $foodMenu = FoodMenu::find($id);
        return $foodMenu->delete();
    }

    public function bulk_destroy(Request $request)
    {
        $product = FoodMenu::whereIn('id' ,$request->ids)->delete();

        return (bool)$product;
    }
}
