<?php

namespace Modules\Restaurant\Http\Controllers\Tenant\Backend;

use App\Helpers\ResponseMessage;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Modules\CountryManage\Entities\State;

class PickupAddressController extends Controller
{
    private const BASE_PATH = 'restaurant::backend.pickup-address.';
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function pickup_address()
    {
        $states = State::all();

        return view(self::BASE_PATH . "index",compact('states'));
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
    public function update_pickup_address(Request $request)
    {
        $nonlang_fields = [
            'menu_pickup_title' => 'required',
            'menu_pickup_state' => 'required',
            'menu_pickup_city' => 'required',
            'menu_pickup_address' => 'required',
        ];

        $this->validate($request,$nonlang_fields);

        foreach ($nonlang_fields as $field_name => $rules){

            update_static_option($field_name,$request->$field_name);
        }

        return response()->success(ResponseMessage::SettingsSaved());
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
}
