<?php

namespace Modules\Restaurant\Http\Controllers\Tenant\Frontend;
use App\Helpers\Payment\PaymentGatewayCredential;
use App\Traits\PaymentLogIpn;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\HotelBooking\Http\Services\ServicesHelpers;
use Modules\Restaurant\Entities\RestaurantPaymentLog;
use Modules\Restaurant\Http\Enums\PaymentRedirectEnum;
use Modules\Restaurant\Http\Requests\MenuOrder\MenuOrderRequest;
use Modules\Restaurant\Http\Services\Admin\RestaurantOrderService;
use Modules\Restaurant\Http\Services\Frontend\MenuOrderPaymentLogUpdate;

class RestaurantPaymentController extends Controller
{
    use PaymentLogIpn;

    private const SUCCESS_ROUTE = 'tenant.frontend.restaurant.payment.success';
    private const CANCEL_ROUTE = 'tenant.frontend.restaurant.payment.cancel';
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('restaurant::index');
    }

    public function paypal_ipn()
    {
        $paypal = PaymentGatewayCredential::get_paypal_credential();
        $payment_data = $paypal->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function paytm_ipn()
    {
        $paytm = PaymentGatewayCredential::get_paytm_credential();
        $payment_data = $paytm->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function flutterwave_ipn()
    {
        $flutterwave = PaymentGatewayCredential::get_flutterwave_credential();
        $payment_data = $flutterwave->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function stripe_ipn()
    {
        $stripe = PaymentGatewayCredential::get_stripe_credential();
        $payment_data = $stripe->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function razorpay_ipn()
    {
        $razorpay = PaymentGatewayCredential::get_razorpay_credential();
        $payment_data = $razorpay->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function paystack_ipn()
    {
        $paystack = PaymentGatewayCredential::get_paystack_credential();
        $payment_data = $paystack->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function payfast_ipn()
    {
        $payfast = PaymentGatewayCredential::get_payfast_credential();
        $payment_data = $payfast->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function mollie_ipn()
    {
        $mollie = PaymentGatewayCredential::get_mollie_credential();
        $payment_data = $mollie->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function midtrans_ipn()
    {
        $midtrans = PaymentGatewayCredential::get_midtrans_credential();
        $payment_data = $midtrans->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function cashfree_ipn()
    {
        $cashfree = PaymentGatewayCredential::get_cashfree_credential();
        $payment_data = $cashfree->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function instamojo_ipn()
    {
        $instamojo = PaymentGatewayCredential::get_instamojo_credential();
        $payment_data = $instamojo->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function marcadopago_ipn()
    {
        $marcadopago = PaymentGatewayCredential::get_marcadopago_credential();
        $payment_data = $marcadopago->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function squareup_ipn()
    {
        $squareup = PaymentGatewayCredential::get_squareup_credential();
        $payment_data = $squareup->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function cinetpay_ipn()
    {
        $cinetpay = PaymentGatewayCredential::get_cinetpay_credential();
        $payment_data = $cinetpay->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function paytabs_ipn()
    {
        $paytabs = PaymentGatewayCredential::get_paytabs_credential();
        $payment_data = $paytabs->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function billplz_ipn()
    {
        $billplz = PaymentGatewayCredential::get_billplz_credential();
        $payment_data = $billplz->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function zitopay_ipn()
    {
        $zitopay = PaymentGatewayCredential::get_zitopay_credential();
        $payment_data = $zitopay->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function toyyibpay_ipn()
    {
        $toyyibpay = PaymentGatewayCredential::get_toyyibpay_credential();
        $payment_data = $toyyibpay->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function pagali_ipn()
    {
        $pagali_ipn = PaymentGatewayCredential::get_pagali_credential();
        $payment_data = $pagali_ipn->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function authorizenet_ipn()
    {
        $authorize_ipn = PaymentGatewayCredential::get_authorizenet_credential();
        $payment_data = $authorize_ipn->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function sitesway_ipn()
    {
        $sitesway_ipn = PaymentGatewayCredential::get_sitesway_credential();
        $payment_data = $sitesway_ipn->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function kinetic_ipn()
    {
        $kinetic_ipn = PaymentGatewayCredential::get_kinetic_credential();
        $payment_data = $kinetic_ipn->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    private function common_ipn_data($payment_data)
    {
        if (isset($payment_data['status']) && $payment_data['status'] === 'complete')
        {
          MenuOrderPaymentLogUpdate::update_database($payment_data['order_id'], $payment_data['transaction_id'] ?? Str::random(15));
            $order_id = wrap_random_number($payment_data['order_id']);
            try {
                MenuOrderPaymentLogUpdate::send_order_mail($payment_data['order_id']);
            }catch (\Exception $e){
              //do nothing
            }
            return redirect()->route(PaymentRedirectEnum::SUCCESS, $order_id)->with('data',$payment_data);
        }

        return redirect()->back()->with(ServicesHelpers::send_response(false,"create"));
    }

    public function order_store(MenuOrderRequest $request)
    {
        if($request->ship_to_a_different_location)
        {
             $request->validate([
                'shipping_full_name'=>'required',
                'shipping_email'=>'required|email',
                'shipping_phone'=>'required',
                'shipping_state'=>'required',
                'shipping_city'=>'required',
                'shipping_address'=>'required',
            ]);
        }

        $menuOrderInfo = $request->all();
        $menuOrderInfo['lang'] = get_user_lang();

        if (!Auth::guard('web')->check())
        {
            return redirect()->back()->withErrors(["msg" => "Please Login first to success your order"]);
        }

        DB::beginTransaction();
        try {
            //Store menu Order
            $menuOrder  = RestaurantOrderService::createMenuOrder($menuOrderInfo);
            //Store menu Order details
            $menuOrderDetails  = RestaurantOrderService::createMenuOrderDetails($menuOrder);
            //Store RestaurantPaymentLog
            $payment_details  = RestaurantOrderService::createRestaurantPaymetLog($menuOrderInfo, $menuOrder);
            //Store belling infos
            $billing_address  = RestaurantOrderService::createbillingInfo($menuOrderInfo, $menuOrder);
            //Store shipping infos
            $billing_address  = RestaurantOrderService::createshippingInfo($menuOrderInfo, $menuOrder);

            Cart::instance('restaurant')->destroy();

            DB::commit();
        } catch (\Exception $e) {
            dd($e->getMessage());
            DB::rollBack();
            Log::error('Error: ' . $e->getMessage());
            return redirect()->back()->with(ServicesHelpers::send_response(false,"create"));
        }
        DB::beginTransaction();
        try {
            $payment_gateways  = RestaurantOrderService::paymentGateways($request,$menuOrder);
            DB::commit();
            return $payment_gateways;
        }catch(\Exception $e)
        {
            dd($e->getMessage());
            DB::rollBack();
            Log::error('Error: ' . $e->getMessage());
            return redirect()->back()->with(ServicesHelpers::send_response(false,"create"));
        }
        session()->put(["type"=>"success"]);

        $data =  RestaurantPaymentLog::findOrFail($payment_details->id);

        return redirect()->route("tenant.frontend.menu.confirmation")->with('data',$payment_details);
    }

}
