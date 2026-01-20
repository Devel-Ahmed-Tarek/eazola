<?php

namespace Modules\Restaurant\Http\Controllers\Tenant\Backend;

use App\Facades\GlobalLanguage;
use App\Helpers\FlashMsg;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Restaurant\Entities\MenuTax;
use Modules\Restaurant\Http\Requests\MenuTax\MenuTaxRequest;
use Modules\Restaurant\Http\Services\Admin\MenuTaxService;


class TaxManageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:restaurant-menu-tax-all|restaurant-menu-tax-create|restaurant-menu-tax-update|restaurant-menu-tax-delete',['only' => 'index']);
        $this->middleware('permission:restaurant-menu-tax-create',['only' => 'store']);
        $this->middleware('permission:restaurant-menu-tax-update',['only' =>'update']);
        $this->middleware('permission:restaurant-menu-tax-delete',['only' => 'destroy']);
        $this->middleware('permission:restaurant-menu-tax-bulk_action',['only' => 'bulk_action']);
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $all_menu_tax = MenuTax::get();
        $default_lang = $request->lang ?? GlobalLanguage::default_slug();

        return view('restaurant::backend.menu-tax.all')->with([
            'all_menu_tax' => $all_menu_tax,
            'default_lang' => $default_lang
        ]);

    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('restaurant::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(MenuTaxRequest $request)
    {
        $data = $request->validated();
        $data['lang'] = $request->lang;
        try {
            $menu_tax = MenuTaxService::createOrUpdate($data);
        } catch (\Exception $e) {

            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return $menu_tax
            ? back()->with(FlashMsg::create_succeed(__('Menu Tax')))
            : back()->with(FlashMsg::create_failed(__('Menu Tax')));
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
        $menu_tax = MenuTax::findOrFail($request->id);
        try {
            $menu_tax = MenuTaxService::createOrUpdate($request,$menu_tax);
        } catch (\Exception $e) {

            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return $menu_tax
            ? back()->with(FlashMsg::update_succeed(__('Menu Tax')))
            : back()->with(FlashMsg::update_failed(__('Menu Tax')));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(MenuTax $item)
    {
        return $item->delete();
    }

    public function bulk_action(Request $request)
    {
        MenuTax::WhereIn('id', $request->ids)->delete();
        return response()->json(['status' => 'ok']);
    }
}
