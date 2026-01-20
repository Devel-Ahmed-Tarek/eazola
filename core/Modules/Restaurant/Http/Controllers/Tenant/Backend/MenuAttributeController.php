<?php

namespace Modules\Restaurant\Http\Controllers\Tenant\Backend;

use App\Helpers\FlashMsg;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HotelBooking\Http\Services\ServicesHelpers;
use Modules\Restaurant\Entities\MenuAttribute;
use Modules\Restaurant\Http\Requests\Menuattribute\MenuAttributeRequest;
use Modules\Restaurant\Http\Services\Admin\AttributeCreateOrUpdate;

class MenuAttributeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:restaurant-menu-attribute-all|restaurant-menu-attribute-create|restaurant-menu-attribute-edit|restaurant-menu-attribute-delete',['only' => 'index']);
        $this->middleware('permission:restaurant-menu-attribute-create',['only' => 'create','store']);
        $this->middleware('permission:restaurant-menu-attribute-edit',['only' => 'edit','update']);
        $this->middleware('permission:restaurant-menu-attribute-delete',['only' => 'destroy']);
        $this->middleware('permission:restaurant-menu-attribute-bulk_action',['only' => 'bulk_action']);
    }

    private const BASE_PATH = 'restaurant::backend.menu-attribute.';
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $all_attributes = MenuAttribute::all();

        return view(self::BASE_PATH . "all-attribute", compact('all_attributes'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view(self::BASE_PATH . 'new-attribute');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(MenuAttributeRequest $request)
    {
        $menu_attribute = AttributeCreateOrUpdate::createOrUpdate($request);

        return redirect(route('tenant.admin.menu.attributes.all'))->with(ServicesHelpers::send_response($menu_attribute,"create"));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(MenuAttribute $item)
    {
        return view(self::BASE_PATH . 'edit-attribute')->with(['attribute' => $item]);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(MenuAttributeRequest $request)
    {
        $menu_attribute = MenuAttribute::find($request->id);
        $updated = AttributeCreateOrUpdate::createOrUpdate($request,$menu_attribute);

        return redirect(route('tenant.admin.menu.attributes.all'))->with(ServicesHelpers::send_response($updated,"update"));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(MenuAttribute $item)
    {
        return $item->delete();
    }

    public function bulk_action(Request $request)
    {
        MenuAttribute::whereIn('id', $request->ids)->delete();
        return back()->with(FlashMsg::item_delete());
    }
}
