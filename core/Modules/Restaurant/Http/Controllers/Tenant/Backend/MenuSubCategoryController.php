<?php

namespace Modules\Restaurant\Http\Controllers\Tenant\Backend;

use App\Facades\GlobalLanguage;
use App\Helpers\FlashMsg;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Restaurant\Entities\MenuCategory;
use Modules\Restaurant\Entities\MenuSubCategory;
use Modules\Restaurant\Http\Requests\MenuCreateOrUpdateRequest;
use Modules\Restaurant\Http\Services\Admin\MenuSubCategoryService;

class MenuSubCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:restaurant-menu-sub-category-all|restaurant-menu-sub-category-create|restaurant-menu-sub-category-edit|restaurant-menu-sub-category-delete',['only' => 'index']);
        $this->middleware('permission:restaurant-menu-subcategory-create',['only' => 'create','store']);
        $this->middleware('permission:restaurant-menu-subcategory-edit',['only' => 'edit','update']);
        $this->middleware('permission:restaurant-menu-sub-category-delete',['only' => 'destroy']);
        $this->middleware('permission:restaurant-menu-subcategory-bulk_action',['only' => 'bulk_action']);

        $this->middleware('permission:restaurant-menu-subcategory-trash-all|restaurant-menu-subcategory-trash_restore|restaurant-menu-subcategory-trash_restore|restaurant-menu-subcategory-trash_delete|restaurant-menu-subcategory-trash_bulk_delete',['only' => 'trash']);
        $this->middleware('permission:restaurant-menu-subcategory-trash_restore',['only' => 'trash_restore']);
        $this->middleware('permission:restaurant-menu-subcategory-trash_delete',['only' => 'trash_delete']);
        $this->middleware('permission:restaurant-menu-subcategory-trash_bulk_delete',['only' => 'trash_bulk_delete']);
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $data = [];
        $data['all_category'] = MenuCategory::get();
        $data['all_sub_category'] = MenuSubCategory::with('menu_category')->get();
        $default_lang = $request->lang ?? GlobalLanguage::default_slug();

        return view('restaurant::backend.menu-subcategory.all',compact('data','default_lang'));

    }

    public function store(MenuCreateOrUpdateRequest $request)
    {
        $menusubcategory = MenuSubCategoryService::createOrUpdate($request);

        return $menusubcategory
            ? back()->with(FlashMsg::create_succeed(__('Menu Sub Category')))
            : back()->with(FlashMsg::create_failed(__('Menu Sub Category')));
    }

    public function update(MenuCreateOrUpdateRequest $request)
    {
        $subcategory = MenuSubCategory::findOrFail($request->id);
        $menusubcategory = MenuSubCategoryService::createOrUpdate($request, $subcategory);

        return $menusubcategory
            ? back()->with(FlashMsg::update_succeed(__('Product Sub Category')))
            : back()->with(FlashMsg::update_failed(__('Product Sub Category')));
    }

    public function destroy(MenuSubCategory $item)
    {
        return $item->delete();
    }

    public function bulk_action(Request $request): JsonResponse
    {
        MenuSubCategory::WhereIn('id', $request->ids)->delete();
        return response()->json(['status' => 'ok']);
    }

    public function trash()
    {
        $all_subcategory = MenuSubCategory::onlyTrashed()->get();

        return view('restaurant::backend.menu-subcategory.trash',compact('all_subcategory'));
    }

    public function trash_restore($id)
    {
        $restored = MenuSubCategory::onlyTrashed()->findOrFail($id)->restore();

        return $restored
            ? back()->with(FlashMsg::restore_succeed(__('Menu Sub Category')))
            : back()->with(FlashMsg::restore_failed(__('Menu Sub Category')));
    }

    public function trash_delete($id)
    {
        $deleted= MenuSubCategory::onlyTrashed()->findOrFail($id)->forceDelete();

        return $deleted
            ? back()->with(FlashMsg::delete_succeed(__('Menu Sub Category')))
            : back()->with(FlashMsg::delete_failed(__('Menu Sub Category')));
    }

    public function trash_bulk_delete(Request $request)
    {
        MenuSubCategory::onlyTrashed()->WhereIn('id', $request->ids)->forceDelete();
        return response()->json(['status' => 'ok']);
    }
}
