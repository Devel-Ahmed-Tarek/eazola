<?php

namespace Modules\Restaurant\Http\Controllers\Tenant\Backend;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HotelBooking\Http\Services\ServicesHelpers;
use Modules\Restaurant\Entities\MenuOrder;

class MenuOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:restaurant-menu-pending-orders|restaurant-menu-approved-orders|restaurant-menu-cancel-requested-orders|restaurant-menu-canceled-orders|restaurant-menu-order_report',['only' => 'index']);
        $this->middleware('permission:restaurant-menu-order-status-update',['only' =>'order_status_update']);
        $this->middleware('permission:restaurant-menu-order-report_search_export',['only' =>'report_search']);

    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $menu_order_infos = MenuOrder::with(["menu_billing",'payment_log',"user","menu_details","menu_shipping"])
            ->orderBy('id','desc')
            ->where('status',0)
            ->orWhere('status', 2)
            ->get();

        return view("restaurant::backend.Menu-order.index",compact("menu_order_infos"));
    }

    public function order_status_update(Request $request)
    {
        // make validation
        $request->validate([
            "order_id" => "required",
            "status" => "required"
        ]);
        // update payment status information
        $bool = MenuOrder::where("id",$request->order_id)->update(["status" => $request->status]);

        return back()->with(ServicesHelpers::send_response($bool,"update"));
    }

    public function approved_order()
    {
        $menu_order_infos = MenuOrder::with(["menu_billing",'payment_log',"user","menu_details"])
            ->orderBy('id','desc')
            ->where('status',1)->get();

        return view("restaurant::backend.Menu-order.index",compact("menu_order_infos"));
    }

    public function canceled_order()
    {
        $menu_order_infos = MenuOrder::with(["menu_billing",'payment_log',"user","menu_details"])
            ->orderBy('id','desc')
            ->where('status',3)->get();

        return view("restaurant::backend.Menu-order.index",compact("menu_order_infos"));
    }

    public function cancel_requested_order()
    {
        $menu_order_infos = MenuOrder::with(["menu_billing",'payment_log',"user","menu_details"])
            ->orderBy('id','desc')
            ->where('status',4)->get();

        return view("restaurant::backend.Menu-order.index",compact("menu_order_infos"));
    }

    public function order_report()
    {
        return view("restaurant::backend.report.index");
    }

    public function report_search(Request $request)
    {
        // make validation here first
        $request->validate([
            "start_date" => "required",
            "end_date" => "required"
        ]);
        $orderInformations = MenuOrder::with(["menu_billing","menu_shipping",'payment_log',"user","menu_details"])->
        whereBetween("created_at",[$request->start_date,$request->end_date])->latest()->get()->groupBy(function ($query){
            return $query->created_at->format("d F Y");
        });

        if($orderInformations->isEmpty()){
            return response()->json([
                'type' => 'worning',
                'warning_msg' => __('No Order on this date range ')
            ]);
        }

        $filename = uniqid();

        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'. $filename .'.csv"',
        );

        return view("restaurant::backend.report.report-table",compact("orderInformations"))->render();
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
