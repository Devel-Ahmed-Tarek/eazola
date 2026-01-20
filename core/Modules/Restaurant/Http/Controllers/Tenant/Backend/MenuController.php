<?php

namespace Modules\Restaurant\Http\Controllers\Tenant\Backend;

use App\Facades\GlobalLanguage;
use App\Models\Status;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Http\Services\Admin\AdminProductServices;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request): Renderable
    {
        $products = AdminProductServices::productSearch($request);
        $statuses = Status::all();
        $default_lang = $request->lang ?? GlobalLanguage::default_slug();
        return view('product::index',compact("products","statuses","default_lang"));
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
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('restaurant::edit');
    }

}
