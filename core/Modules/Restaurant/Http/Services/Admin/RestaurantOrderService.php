<?php

namespace Modules\Restaurant\Http\Services\Admin;
use App\Helpers\Payment\PaymentGatewayCredential;
use App\Helpers\SanitizeInput;
use Carbon\Carbon;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Modules\Restaurant\Entities\FoodMenuAttribute;
use Modules\Restaurant\Entities\MenuBillingInfo;
use Modules\Restaurant\Entities\MenuOrder;
use Modules\Restaurant\Entities\MenuOrderDetail;
use Modules\Restaurant\Entities\MenuOrderDetailAttribute;
use Modules\Restaurant\Entities\MenuShippingInfo;
use Modules\Restaurant\Entities\RestaurantPaymentLog;


class RestaurantOrderService
{
    private const SUCCESS_ROUTE = 'tenant.frontend.hotel-booking.payment.success';
    private const CANCEL_ROUTE = 'tenant.frontend.hotel-booking.payment.cancel';

    public static function createMenuOrder($menuOrderInfo)
    {
        $MenuOrder = new MenuOrder();
        $MenuOrder->user_id = Auth::guard('web')->user()?Auth::guard('web')->user()->id :"";
        $MenuOrder->order_date = self::clean_date(Carbon::now());
        $MenuOrder->delivery_option = $menuOrderInfo['delivery_option'];
        $MenuOrder->payment_status = 0;
        $MenuOrder->payment_gateway = $menuOrderInfo['selected_payment_gateway'];
        $MenuOrder->payment_track = Str::random(10) . Str::random(10);
        $MenuOrder->transaction_id = Str::random(10) . Str::random(10);
        $MenuOrder->subtotal = $menuOrderInfo['subtotal'];
        $MenuOrder->tax_amount = $menuOrderInfo['total_tax'];
        $MenuOrder->total_amount = $menuOrderInfo['total'];
        $MenuOrder->save();

        return $MenuOrder;
    }

    public static function createbillingInfo($menuOrderInfo, $menuOrder)
    {
        $menu_billing = new MenuBillingInfo();
        $menu_billing->name = $menuOrderInfo['full_name'];
        $menu_billing->email = $menuOrderInfo['email'];
        $menu_billing->phone = $menuOrderInfo['phone'];
        $menu_billing->address = $menuOrderInfo['address'];
        $menu_billing->address_two = $menuOrderInfo['address_two'];
        $menu_billing->state_id = $menuOrderInfo['state'];
        $menu_billing->menu_order_id = $menuOrder->id;
        $menu_billing->setTranslation('city',$menuOrderInfo['lang'], SanitizeInput::esc_html($menuOrderInfo['city']));
        $menu_billing->save();
        return $menu_billing;
    }

    public static function createshippingInfo($menuOrderInfo, $menuOrder)
    {
        $menu_billing = new MenuShippingInfo();
        if(@$menuOrderInfo['ship_to_a_different_location'])
        {
            $menu_billing->name = $menuOrderInfo['shipping_full_name'];
            $menu_billing->email = $menuOrderInfo['shipping_email'];
            $menu_billing->phone = $menuOrderInfo['shipping_phone'];
            $menu_billing->address = $menuOrderInfo['shipping_address'];
            $menu_billing->state_id = $menuOrderInfo['shipping_state'];
            $menu_billing->menu_order_id = $menuOrder->id;
            $menu_billing->setTranslation('city',$menuOrderInfo['lang'], SanitizeInput::esc_html($menuOrderInfo['shipping_city']));
        }else
        {
            $menu_billing->name = $menuOrderInfo['full_name'];
            $menu_billing->email = $menuOrderInfo['email'];
            $menu_billing->phone = $menuOrderInfo['phone'];
            $menu_billing->address = $menuOrderInfo['address'];
            $menu_billing->state_id = $menuOrderInfo['state'];
            $menu_billing->menu_order_id = $menuOrder->id;
            $menu_billing->setTranslation('city',$menuOrderInfo['lang'], SanitizeInput::esc_html($menuOrderInfo['city']));
        }
        $menu_billing->save();

        return $menu_billing;
    }

    public static function createMenuOrderDetails($menuOrder)
    {
        $cartItems = Cart::instance('restaurant')->content();
        $menuOrderDetails = new MenuOrderDetail();

        foreach ($cartItems as $item)
        {
            $menuOrderDetails =  MenuOrderDetail::create([
                "food_menu_id" =>$item->id,
                "name" =>$item->name,
                "price" =>$item->price,
                "menu_order_id" =>$menuOrder->id,
                "quantity" =>$item->qty,
                "menu_tax" =>$item->options->tax,
                "image" =>$item->options->image
            ]);

            $attribute_ids = explode(",",$item->options->attribute_ids);
            self::createOrderDetailAttribute($attribute_ids, $menuOrderDetails);

        }

        return $menuOrderDetails;
    }

    public static function createRestaurantPaymetLog($menuOrderInfo,$menuOrder)
    {
        $payment_details = RestaurantPaymentLog::create([
            'name' => $menuOrderInfo['full_name'],
            'email' => $menuOrderInfo['email'] ?? "user@gmail.com",
            'phone' => $menuOrderInfo['phone'] ?? "000000",
            'order_status' => 1,
            'tax_amount' => $menuOrderInfo['total_tax'],
            'subtotal' => $menuOrderInfo['subtotal'],
            'total_amount' => $menuOrderInfo['total'],
            'user_id' => Auth()->guard('web')->user()->id ?? 100,
            'coupon_type' => null,
            'coupon_code' => null,
            'coupon_discount' => null,
            'payment_gateway' => $menuOrderInfo['selected_payment_gateway'],
            'payment_status' => "pending",
            'status' => 0,
            'menu_order_id' => $menuOrder->id,
        ]);

        return $payment_details;
    }

    private static function clean_date($date)
    {
        return Carbon::parse(str_replace(" 00:00:00","",$date))->format("Y-m-d");
    }

    private static function createOrderDetailAttribute($attribute_ids, $orderDetail_id)
    {
        foreach ($attribute_ids ?? [] as $id)
        {
            $menu_attribute = FoodMenuAttribute::where('id',$id)->first();

            MenuOrderDetailAttribute::create([
                'term'=>$menu_attribute->term,
                'value'=>$menu_attribute->value,
                'additional_price'=>$menu_attribute->extra_price,
                'menu_order_detail_id'=>$orderDetail_id->id
            ]);
        }
    }

    public static function paymentGateways($request,$payment_details)
    {
        if ($request['selected_payment_gateway'] === 'paypal') {
            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.paypal.ipn'));
            $paypal = PaymentGatewayCredential::get_paypal_credential();
            return $paypal->charge_customer($params);
        }
        elseif ($request['selected_payment_gateway'] === 'paytm') {

            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.paytm.ipn'));
            $paytm = PaymentGatewayCredential::get_paytm_credential();
            return $paytm->charge_customer($params);

        } elseif ($request['selected_payment_gateway'] === 'mollie') {

            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.mollie.ipn'));
            $mollie = PaymentGatewayCredential::get_mollie_credential();
            return $mollie->charge_customer($params);

        } elseif ($request['selected_payment_gateway'] === 'stripe') {
            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.stripe.ipn'));
            $stripe = PaymentGatewayCredential::get_stripe_credential();
            return $stripe->charge_customer($params);

        } elseif ($request['selected_payment_gateway'] === 'razorpay') {

            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.razorpay.ipn'));
            $razorpay = PaymentGatewayCredential::get_razorpay_credential();
            return $razorpay->charge_customer($params);

        } elseif ($request['selected_payment_gateway'] === 'flutterwave') {

            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.flutterwave.ipn'));
            $flutterwave = PaymentGatewayCredential::get_flutterwave_credential();
            return $flutterwave->charge_customer($params);

        } elseif ($request['selected_payment_gateway'] === 'paystack') {

            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.paystack.ipn'));
            $paystack = PaymentGatewayCredential::get_paystack_credential();
            return $paystack->charge_customer($params);

        } elseif ($request['selected_payment_gateway'] === 'midtrans') {

            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.midtrans.ipn'));
            $midtrans = PaymentGatewayCredential::get_midtrans_credential();
            return $midtrans->charge_customer($params);

        } elseif ($request['selected_payment_gateway'] == 'payfast') {

            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.payfast.ipn'));
            $payfast = PaymentGatewayCredential::get_payfast_credential();
            return $payfast->charge_customer($params);

        } elseif ($request['selected_payment_gateway'] == 'cashfree') {

            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.cashfree.ipn'));
            $cashfree = PaymentGatewayCredential::get_cashfree_credential();
            return $cashfree->charge_customer($params);

        } elseif ($request['selected_payment_gateway'] == 'instamojo') {

            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.instamojo.ipn'));
            $instamojo = PaymentGatewayCredential::get_instamojo_credential();
            return $instamojo->charge_customer($params);

        } elseif ($request['selected_payment_gateway'] == 'marcadopago') {

            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.marcadopago.ipn'));
            $marcadopago = PaymentGatewayCredential::get_marcadopago_credential();
            return $marcadopago->charge_customer($params);

        }
        elseif($request['selected_payment_gateway'] == 'squareup')
        {
            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.squareup.ipn'));
            $squareup = PaymentGatewayCredential::get_squareup_credential();
            return $squareup->charge_customer($params);
        }

        elseif($request['selected_payment_gateway'] == 'cinetpay')
        {
            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.cinetpay.ipn'));
            $cinetpay = PaymentGatewayCredential::get_cinetpay_credential();
            return $cinetpay->charge_customer($params);
        }

        elseif($request['selected_payment_gateway'] == 'paytabs')
        {
            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.paytabs.ipn'));
            $paytabs = PaymentGatewayCredential::get_paytabs_credential();
            return $paytabs->charge_customer($params);
        }
        elseif($request['selected_payment_gateway'] == 'billplz')
        {
            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.billplz.ipn'));
            $billplz = PaymentGatewayCredential::get_billplz_credential();
            return $billplz->charge_customer($params);
        }
        elseif($request['selected_payment_gateway'] == 'zitopay')
        {
            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.zitopay.ipn'));
            $zitopay = PaymentGatewayCredential::get_zitopay_credential();
            return $zitopay->charge_customer($params);
        }
        elseif($request['selected_payment_gateway'] == 'toyyibpay')
        {
            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.toyyibpay.ipn'));
            $toyyibpay = PaymentGatewayCredential::get_toyyibpay_credential();
            return $toyyibpay->charge_customer($params);
        }
        elseif($request['selected_payment_gateway'] == 'pagali')
        {
            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.pagali.ipn'));
            $pagali = PaymentGatewayCredential::get_pagali_credential();
            return $pagali->charge_customer($params);
        }
        elseif($request['selected_payment_gateway'] == 'authorizenet')
        {
            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.authorizenet.ipn'));
            $authorizenet = PaymentGatewayCredential::get_authorizenet_credential();
            return $authorizenet->charge_customer($params);
        }
        elseif($request['selected_payment_gateway'] == 'sitesway')
        {
            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.sitesway.ipn'));
            $sitesway = PaymentGatewayCredential::get_sitesway_credential();

            return $sitesway->charge_customer($params);
        }
        elseif($request['selected_payment_gateway'] == 'kinetic')
        {
            $params = self::common_charge_customer_data($payment_details['total_amount'],$payment_details,$request,route('tenant.frontend.restaurant.kinetic.ipn'));
            $kinetic = PaymentGatewayCredential::get_kinetic_credential();
            return $kinetic->charge_customer($params);
        }
        elseif ($request['selected_payment_gateway'] == 'bank_transfer')
        {
            $fileName = time().'.'.$request->manual_payment_attachment->extension();

            // Image scan start
            $uploaded_file = $request->manual_payment_attachment;
            $file_extension = $uploaded_file->getClientOriginalExtension();
            if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $processed_image = Image::make($uploaded_file);
                $image_default_width = $processed_image->width();
                $image_default_height = $processed_image->height();
                $processed_image->resize($image_default_width, $image_default_height, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $processed_image->save('assets/uploads/attachment/' . $fileName);
            }else{
                $uploaded_file->move('assets/uploads/attachment/',$fileName);
            } // Image scan end

            RestaurantPaymentLog::where('id', $payment_details->id)->update([
                'status' => 'pending',
                'manual_payment_attachment' => $fileName
            ]);

            $customer_subject = __('Your Restaurant Food Order payment sent and it is in admin approval stage..!').' '.get_static_option('site_'.get_user_lang().'_title');
            $admin_subject = __('You have a new  Restaurant Food order with bank transfer, please check and approve..!').' '.get_static_option('site_'.get_user_lang().'_title');

            try {
            } catch (\Exception $e) {
                return redirect()->back()->with(['type' => 'danger', 'msg' => $e->getMessage()]);
            }

            $order_id = Str::random(6) .$payment_details->id . Str::random(6);

        }else if($request['selected_payment_gateway'] == 'manual_payment_')
        {

            $customer_subject = __('Your event payment sent and it is in admin approval stage..!').' '.get_static_option('site_'.get_user_lang().'_title');
            $admin_subject = __('You have a new event with manual payment, please check and approve..!').' '.get_static_option('site_'.get_user_lang().'_title');
            try {

            } catch (\Exception $e) {
                return redirect()->back()->with(['type' => 'danger', 'msg' => $e->getMessage()]);
            }

            RestaurantPaymentLog::where('id', $payment_details->id)->update([
                'transaction_id' => $request->transaction_id,
                'status' => 'pending',
            ]);
            $order_id = Str::random(6) .$payment_details->id . Str::random(6);
            $booking_id = Str::random(6) .$payment_details->id . Str::random(6);
            return redirect()->route(self::SUCCESS_ROUTE,$order_id);

        }else{
            return self::payment_with_gateway($request['selected_payment_gateway'], $payment_details['total_amount'],$payment_details,$request);
        }


    }

    private static function common_charge_customer_data($total_amount,$payment_details,$request,$ipn_url) : array
    {
        $data = [
            'amount' => $payment_details['total_amount'],
            'title' => "Menu Order",
            'description' => 'Payment For Food Menu Order Id: #' . $payment_details->id,
            'Payer Name: ' . $request->name . ' Payer Email:' . $request->email,
            'order_id' => $payment_details->id,
            'track' => $payment_details->id,
            'cancel_url' => route(self::CANCEL_ROUTE),
            'success_url' => route(self::SUCCESS_ROUTE, $payment_details->id),
            'email' => $payment_details->email,
            'name' => $payment_details->full_name,
            'payment_type' => 'Menu Order',
            'ipn_url' => $ipn_url,
        ];

        return $data;
    }

}
