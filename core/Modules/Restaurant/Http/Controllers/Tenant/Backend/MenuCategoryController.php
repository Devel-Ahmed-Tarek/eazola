<?php

namespace Modules\Restaurant\Http\Controllers\Tenant\Backend;

use App\Facades\GlobalLanguage;
use App\Helpers\FlashMsg;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Restaurant\Entities\MenuCategory;
use Modules\Restaurant\Entities\MenuSubCategory;
use Modules\Restaurant\Http\Services\MenuCategoryService;

class MenuCategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:restaurant-menu-category-all|restaurant-menu-category-create|restaurant-menu-category-edit|restaurant-menu-category-delete',['only' => 'index']);
        $this->middleware('permission:restaurant-menu-category-create',['only' => 'create','store']);
        $this->middleware('permission:restaurant-menu-category-edit',['only' => 'edit','update']);
        $this->middleware('permission:restaurant-menu-category-delete',['only' => 'destroy']);
        $this->middleware('permission:restaurant-menu-category-bulk_delete',['only' => 'bulk_action']);

        $this->middleware('permission:restaurant-menu-category-trash-all|restaurant-menu-category-trash_restore|restaurant-menu-category-trash_delete|restaurant-menu-category-trash_bulk_delete',['only' => 'trash']);
        $this->middleware('permission:restaurant-menu-category-trash_restore',['only' => 'trash_restore']);
        $this->middleware('permission:restaurant-menu-category-trash_delete',['only' => 'trash_delete']);
        $this->middleware('permission:restaurant-menu-category-trash_bulk_delete',['only' => 'trash_bulk_delete']);
    }

    public function index(Request $request): View|Factory|Application
    {
        $all_category = MenuCategory::get();
        $default_lang = $request->lang ?? GlobalLanguage::default_slug();

        return view('restaurant::backend.menu-category.all')->with(['all_category' => $all_category,'default_lang' => $default_lang]);
    }

    public function getSubCategory(Request $req)
    {
        // fetch sub category from category
        $categories = MenuSubCategory::where("menu_category_id" , $req->category_id)->get();

        // load view file for select option
        $default_lang = $req->lang ?? GlobalLanguage::default_slug();
        $options = view("restaurant::backend.menu-category.option", compact("categories","default_lang"))->render();
        return response()->json(["success" => true,"html" => $options]);
    }

    public function create()
    {
        return view('restaurant::create');
    }

    public function trash(): View
    {
        $all_category = MenuCategory::onlyTrashed()->get();
        return view('restaurant::backend.menu-category.trash')->with(['all_category' => $all_category]);
    }

    public function trash_delete($id)
    {
        $deleted= MenuCategory::onlyTrashed()->findOrFail($id)->forceDelete();

        return $deleted
            ? back()->with(FlashMsg::delete_succeed(__('Menu Category')))
            : back()->with(FlashMsg::delete_failed(__('Menu Category')));
    }

    public function trash_restore($id)
    {
        $restored = MenuCategory::onlyTrashed()->findOrFail($id)->restore();

        return $restored
            ? back()->with(FlashMsg::restore_succeed(__('Menu Category')))
            : back()->with(FlashMsg::restore_failed(__('Menu Category')));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        try {
            $category = MenuCategoryService::createOrUpdate($request);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);

        }

        return $category
            ? back()->with(FlashMsg::create_succeed(__('Menu Category')))
            : back()->with(FlashMsg::create_failed(__('Menu Category')));
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
        return view('restaurant::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request)
    {
        $menuCategory = MenuCategory::findOrFail($request->id);
        try {
            $category = MenuCategoryService::createOrUpdate($request,$menuCategory);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);

        }
        return $category
            ? back()->with(FlashMsg::update_succeed(__('Menu Category')))
            : back()->with(FlashMsg::update_failed(__('Menu Category')));

    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(MenuCategory $item)
    {
        return $item->delete();
    }

    public function bulk_action(Request $request)
    {
        MenuCategory::WhereIn('id', $request->ids)->delete();
        return response()->json(['status' => 'ok']);
    }

    public function trash_bulk_delete(Request $request)
    {
        MenuCategory::onlyTrashed()->WhereIn('id', $request->ids)->forceDelete();
        return response()->json(['status' => 'ok']);
    }

}
