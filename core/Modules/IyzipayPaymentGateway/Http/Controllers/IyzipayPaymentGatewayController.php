<?php

namespace Modules\IyzipayPaymentGateway\Http\Controllers;

use App\Enums\PaymentRouteEnum;
use App\Events\TenantNotificationEvent;
use App\Helpers\FlashMsg;
use App\Helpers\Payment\DatabaseUpdateAndMailSend\LandlordPricePlanAndTenantCreate;
use App\Helpers\Payment\DatabaseUpdateAndMailSend\Tenant\TenantAppointment;
use App\Helpers\Payment\DatabaseUpdateAndMailSend\Tenant\TenantDonation;
use App\Helpers\Payment\DatabaseUpdateAndMailSend\Tenant\TenantEvent;
use App\Helpers\Payment\PaymentGatewayCredential;
use App\Mail\BasicMail;
use App\Models\PaymentLogs;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\DomainReseller\Entities\DomainPaymentLog;
use Modules\Product\Entities\ProductOrder;
use Xgenious\Paymentgateway\Facades\XgPaymentGateway;

class IyzipayPaymentGatewayController extends Controller
{
    private const SUCCESS_ROUTE = 'landlord.frontend.order.payment.success';
    private const STATIC_CANCEL_ROUTE = 'landlord.frontend.order.payment.cancel.static';

    public function chargeCustomer($args)
    {
        //detect it is coming from which method for which kind of payment
        //detect it for landlord or tenant website
        if (in_array($args["payment_type"], ["price_plan", "deposit"]) && $args["payment_for"] === "landlord") {
            session()->put('payment_for', 'landlord');
            session()->put('payment_user_type', 'landlord');

            return $this->chargeCustomerForLandlordPricePlanPurchase($args);
        }

        // all tenant payment process will from here....
        if (in_array($args["payment_type"], ["domain-reseller","shop_checkout","event","donation","appointment"]) && $args["payment_for"] === "tenant") {
            session()->put('payment_for', 'tenant');
            session()->put('payment_type', $args["payment_type"]);

            return $this->chargeCustomerForLandlordPricePlanPurchase($args);
        }

        abort(404);
        //make a request to Siteways server to generate checkout url based on static data
    }

    public function get_iyzipay_credential(): object
    {
        $test_mode = get_static_option('iyzipay_payment_gateway_test_mode');
        $secret_key = get_static_option('iyzipay_payment_gateway_secret_key');
        $api_key = get_static_option('iyzipay_payment_gateway_api_key');

        $iyzipay = XgPaymentGateway::iyzipay();
        $iyzipay->setSecretKey($secret_key);
        $iyzipay->setApiKey($api_key);
        $iyzipay->setEnv($test_mode);
        $iyzipay->setCurrency(site_global_currency());

        return $iyzipay;
    }

    private function chargeCustomerForLandlordPricePlanPurchase($args)
    {

        $param_data = $this->common_charge_customer_data(
            $args['total'],
            $args['payment_details'],
            $args['request'],
            route(route_prefix() . 'plugin.iyzipay.ipn'),
            $args['type'] ?? null
        );


        $iyzipay_gateway = $this->get_iyzipay_credential();



        return $iyzipay_gateway->charge_customer($param_data);
    }

    /**
     * @param $amount_to_charge
     * @param $payment_details
     * @param $request
     * @param $ipn_url
     * @return array
     * @see common_charge_customer_data
     */
    private function common_charge_customer_data($amount_to_charge, $payment_details, $request, $ipn_url, $type = null): array
    {
        $payment_for = session('payment_for');
        $payment_type = session('payment_type');

        $data = [];


        if ($payment_for === 'landlord')
        {
            $data = [
                'amount' => $amount_to_charge ?? 1,
                'title' => $payment_details['package_name'],
                'description' => 'Payment For Package Order Id: #' . $request->package_id . ' Package Name: ' . $payment_details['package_name'] .
                 'Payer Name: ' . $request->name . ' Payer Email:' . $request->email,
                'order_id' => $payment_details['id'],
                'track' => $payment_details['track'],
                'cancel_url' => route(self::STATIC_CANCEL_ROUTE),
                'success_url' => route(self::SUCCESS_ROUTE, $payment_details['id']),
                'email' => $payment_details['email'],
                'name' => $payment_details['name'],
                'payment_type' => 'order',
                'ipn_url' => $ipn_url,
            ];
        } else {

            if ($payment_type === 'shop_checkout')
            {
                $data = [
                    'amount' => $amount_to_charge,
                    'title' => 'Order ID: ' . $payment_details['id'],
                    'description' => 'Payment For Order ID: #' . $payment_details['id'] .
                    ' Payer Name: ' . $payment_details['name'] .
                    ' Payer Email: ' . $payment_details['email'],
                    'order_id' => $payment_details['id'],
                    'track' => $payment_details['payment_track'] ?? ($payment_details['track'] ?? ""),
                    'cancel_url' => route(PaymentRouteEnum::CANCEL_ROUTE, $payment_details['id']),
                    'success_url' => route(PaymentRouteEnum::SUCCESS_ROUTE, $payment_details['id']),
                    'email' => $payment_details['email'],
                    'name' => $payment_details['name'],
                    'payment_type' => 'order',
                    'ipn_url' => $ipn_url,
                ];
            }
            elseif ($payment_type === 'event')
            {
                $data = [
                    'amount' => $amount_to_charge,
                    'title' => $payment_details['event']['title']['en'] ?? '',
                    'description' => 'Payment For event Id: #' . $payment_details['event_id'] . ' Event Name: ' . $payment_details['event']['title']['en'] ?? '',
                    'Payer Name: ' . $request->name . ' Payer Email:' . $request->email,
                    'order_id' => $payment_details['id'],
                    'track' => $payment_details['track'],
                    'cancel_url' => route('tenant.frontend.event.payment.cancel', $payment_details['id']),
                    'success_url' => route('tenant.frontend.event.payment.success', $payment_details['id']),
                    'email' => $payment_details['email'],
                    'name' => $payment_details['name'],
                    'payment_type' => 'order',
                    'ipn_url' => $ipn_url,
                ];
            }
            elseif ($payment_type === 'appointment')
            {
                $data = [
                    'amount' => $amount_to_charge,
                    'title' => $payment_details['appointment']['title']['en_US'] ?? '',
                    'description' => 'Payment For event Id: #' . $payment_details['appointment_id'] . ' Appointment Name: ' . $payment_details['appointment']['title']['en_US'] ?? '',
                    'Payer Name: ' . $request->name . ' Payer Email:' . $request->email,
                    'order_id' => $payment_details['id'],
                    'track' => Str::random(),
                    'cancel_url' => route('tenant.frontend.appointment.payment.cancel', $payment_details['id']),
                    'success_url' => route('tenant.frontend.appointment.payment.success', $payment_details['id']),
                    'email' => $payment_details['email'],
                    'name' => $payment_details['name'],
                    'payment_type' => 'order',
                    'ipn_url' => $ipn_url,
                ];
            }
            elseif($payment_type === 'donation')
            {
                $data = [
                    'amount' => $amount_to_charge,
                    'title' => $payment_details['donation']['title']['en_US'] ?? '',
                    'description' => 'Payment For donation Id: #' . $payment_details['donation_id'] . ' Donation Name: ' . $payment_details['donation']['title']['en_US'] ?? '',
                    'Payer Name: ' . $request->name . ' Payer Email:' . $request->email,
                    'order_id' => $payment_details['id'],
                    'track' => $payment_details['track'],
                    'cancel_url' => route('tenant.frontend.donation.payment.cancel', $payment_details['id']),
                    'success_url' => route('tenant.frontend.donation.payment.success', $payment_details['id']),
                    'email' => $payment_details['email'],
                    'name' => $payment_details['name'],
                    'payment_type' => 'order',
                    'ipn_url' => $ipn_url,
                ];
            }
            elseif($payment_type === 'domain-reseller')
            {
                session()->put('purchase-option', $type);

                return [
                    'amount' => "$amount_to_charge",
                    'title' => "Order ID: {$payment_details['id']}",
                    'description' => $payment_details,
                    'order_id' => $payment_details['id'],
                    'track' => Str::random(),
                    'cancel_url' => route(\Modules\DomainReseller\Http\Enums\PaymentRouteEnum::CANCEL_ROUTE, wrap_random_number($payment_details['id'])),
                    'success_url' => route(PaymentRouteEnum::SUCCESS_ROUTE, wrap_random_number($payment_details['id'])),
                    'email' => $payment_details['email'],
                    'name' => $payment_details['first_name'],
                    'payment_type' => 'order',
                    'ipn_url' => $ipn_url,
                ];
            }
        }

        return $data;
    }

    public function iyzipay_ipn()
    {
        $iyzipay_ipn = $this->get_iyzipay_credential();

        try {
            $payment_for = session('payment_for');
            $common_ipn_data_function_name = "common_ipn_data_{$payment_for}";

            $payment_data = $iyzipay_ipn->ipn_response();

            return $this->$common_ipn_data_function_name($payment_data);
        } catch (\Exception $e) {
            return to_route('landlord.homepage');
        }
    }

    private function common_ipn_data_landlord($payment_data)
    {
        if (isset($payment_data['status']) && $payment_data['status'] === 'complete') {

            $log = [];
            if (!empty($payment_data['order_id'])) {
                $log = PaymentLogs::find($payment_data['order_id']);
            }
            try {
                LandlordPricePlanAndTenantCreate::update_database($payment_data['order_id'], $payment_data['transaction_id']);
            } catch (\Exception $e) {
                return redirect()->back(FlashMsg::item_delete(__('Something went wrong...!')));
            }
            try {
                LandlordPricePlanAndTenantCreate::tenant_create_event_with_credential_mail($payment_data['order_id']);
                session()->forget('random_password_for_tenant');
            } catch (\Exception $e) {

                $message = $e->getMessage();
                $admin_mail_message = sprintf(__('Database Crating failed for user id %1$s , please checkout admin panel and generate database for this user from admin panel manually'), $log->user_id);
                $admin_mail_subject = sprintf(__('Database Crating failed for user id %1$s'), $log->user_id);
                Mail::to(get_static_option('site_global_email'))->send(new BasicMail($admin_mail_message, $admin_mail_subject));

                if (str_contains($message, 'Access denied')) {
                    LandlordPricePlanAndTenantCreate::store_exception($log->tenant_id, 'domain create failed', $message, 0);

                    //Event Notification
                    $event_data = ['id' => $log->id, 'title' => __('Database and domain create failed'), 'type' => 'new_subscription',];
                    event(new TenantNotificationEvent($event_data));
                    //Event Notification

                    //Store tenant id in session
                    session(['exception_tenant_id' => $log->tenant_id]);
                }

            }

            if (DB::table('domains')->where('tenant_id', $log->tenant_id)->first()) {
                try {
                    LandlordPricePlanAndTenantCreate::update_tenant($payment_data);
                } catch (\Exception $e) {

                    LandlordPricePlanAndTenantCreate::store_exception($log->tenant_id, 'database update', $e->getMessage(), 1);
                }
                try {
                    LandlordPricePlanAndTenantCreate::send_order_mail($payment_data['order_id']);
                } catch (\Exception $e) {

                }
            }

            $order_id = wrap_random_number($payment_data['order_id']);

            if (\session()->has('website_create_type')) {
                $url = DB::table('domains')->where('tenant_id', $log->tenant_id)->first()->domain;
                $url = tenant_url_with_protocol($url);
                \session()->forget('website_create_type');

                return redirect()->to($url);
            }

            return redirect()->route(self::SUCCESS_ROUTE, $order_id);
        }

        return redirect()->route(self::STATIC_CANCEL_ROUTE);
    }

    private function common_ipn_data_tenant($payment_data)
    {
        if (isset($payment_data['status']) && $payment_data['status'] === 'complete')
        {
            $payment_type = session('payment_type');

            if($payment_type === 'shop_checkout')
            {
                (new self())->send_order_mail($payment_data['order_id']);
                $order_id = wrap_random_number($payment_data['order_id']);

                ProductOrder::find($payment_data['order_id'])->update([
                    'payment_status' => 'success'
                ]);

                return redirect()->route(PaymentRouteEnum::SUCCESS_ROUTE, $order_id);
            }
            elseif($payment_type === 'event')
            {
                if (isset($payment_data['status']) && $payment_data['status'] === 'complete') {
                    TenantEvent::update_database($payment_data['order_id'], $payment_data['transaction_id'] ?? Str::random(15));
                    TenantEvent::send_event_mail($payment_data['order_id']);
                    $order_id = wrap_random_number($payment_data['order_id']);

                    return redirect()->route('tenant.frontend.event.payment.success', $order_id);
                }

                return redirect()->route('tenant.frontend.event.payment.cancel');
            }
            elseif ($payment_type === 'donation')
            {
                if (isset($payment_data['status']) && $payment_data['status'] === 'complete') {
                    TenantDonation::update_database($payment_data['order_id'], $payment_data['transaction_id']);
                    TenantDonation::send_donation_mail($payment_data['order_id']);
                    $order_id = wrap_random_number($payment_data['order_id']);

                    return redirect()->route('tenant.frontend.donation.payment.success', $order_id);
                }
                return redirect()->route('tenant.frontend.donation.payment.cancel');
            }
            elseif($payment_type === 'appointment')
            {
                if (isset($payment_data['status']) && $payment_data['status'] === 'complete') {
                    TenantAppointment::update_database($payment_data['order_id'], $payment_data['transaction_id'] ?? Str::random(15));
                    TenantAppointment::send_event_mail($payment_data['order_id']);
                    $order_id = wrap_random_number($payment_data['order_id']);

                    return redirect()->route('tenant.frontend.donation.payment.success', $order_id);
                }
                return redirect()->route('tenant.frontend.donation.payment.cancel');
            }
            elseif ($payment_type === 'domain-reseller')
            {
                session()->forget(['cart_domain_data', 'cart_domain', 'agreementKeys']);
                $new_or_renew = session('purchase-option');

                $domain_payment_log = DomainPaymentLog::find($payment_data['order_id']);

                if (!empty($domain_payment_log))
                {
                    try {
                        $PAYMENT_STATUS = $payment_data['status'] === 'complete';

                        $domain_payment_log->update([
                            'payment_status' => $PAYMENT_STATUS,
                            'track' => $payment_data['transaction_id'],
                            'purchase_count' => $domain_payment_log->purchase_count + 1
                        ]);

                        if ($domain_payment_log['payment_status'])
                        {
                            if ($new_or_renew === 'new')
                            {
                                return $this->domainPurchaseAction($domain_payment_log);
                            }
                            else if($new_or_renew === 'renew')
                            {
                                return $this->domainRenewAction($domain_payment_log);
                            }
                        }

                        return to_route('tenant.admin.domain-reseller.payment.cancel', wrap_random_number($domain_payment_log->id));
                    } catch (\Exception $exception)
                    {

                    }
                }

                return to_route('tenant.admin.domain-reseller.payment.cancel.static');
            }
        }

        return redirect()->route(PaymentRouteEnum::STATIC_CANCEL_ROUTE);
    }

    public function settings()
    {
        $payment_gateway_info = [
            'name' => 'iyzipay',
            'description' => get_static_option('iyzipay_payment_gateway_description'),
            'credentials' => json_encode([
                'secret_key' => get_static_option('iyzipay_payment_gateway_secret_key'),
                'api_key' => get_static_option('iyzipay_payment_gateway_api_key')
            ]),
            'status' => get_static_option('iyzipay_payment_gateway_status'),
            'test_mode' => get_static_option('iyzipay_payment_gateway_test_mode')
        ];

        return view('iyzipaypaymentgateway::landlord.backend.payment-gateway', compact('payment_gateway_info'));
    }

    public function settingsUpdate(Request $request)
    {
        $validated_fields = $request->validate([
            'iyzipay_payment_gateway_status' => 'nullable',
            'iyzipay_payment_gateway_test_mode' => 'nullable',
            'iyzipay_payment_gateway_secret_key' => 'required|string',
            'iyzipay_payment_gateway_api_key' => 'required|string'
        ]);

        $absolute_keys = ['iyzipay_payment_gateway_status', 'iyzipay_payment_gateway_test_mode', 'iyzipay_payment_gateway_secret_key', 'iyzipay_payment_gateway_api_key'];
        abort_if(!empty(array_diff(array_keys($validated_fields), $absolute_keys)), 404);

        foreach ($validated_fields as $field_key => $field_value) {
            update_static_option($field_key, $request->$field_key);
        }

        return redirect()->back()->with([
            'msg' => __('Payment Settings Updated.'),
            'type' => 'success'
        ]);
    }
}
