<?php

namespace Modules\Restaurant\Http\Services\Frontend;
use App\Mail\MenuOrderEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Modules\Restaurant\Entities\MenuOrder;
use Modules\Restaurant\Entities\RestaurantPaymentLog;

class MenuOrderPaymentLogUpdate
{
    public static function update_database($order_id, $transaction_id)
    {
        $event_log = RestaurantPaymentLog::where('menu_order_id',$order_id)->first();
        $event_log->transaction_id = $transaction_id ?? null;
        $event_log->status = 1;
        $event_log->payment_status = "complete";
        $event_log->updated_at = Carbon::now();
        $event_log->save();
    }

    public static function send_order_mail($order_id)
    {
        $order_details = MenuOrder::with(['menu_details','order_attributes','menu_billing','menu_shipping'])->where('id',$order_id)->firstOrFail();
        $order_mail = get_static_option('order_page_form_mail') ?? get_static_option('tenant_site_global_email');

        try {
            Mail::to($order_mail)->send(new MenuOrderEmail($order_details));
        } catch (\Exception $e) {

        }
    }

}
