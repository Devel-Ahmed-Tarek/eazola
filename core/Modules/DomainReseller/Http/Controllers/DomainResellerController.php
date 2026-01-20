<?php

namespace Modules\DomainReseller\Http\Controllers;

use App\Helpers\FlashMsg;
use App\Helpers\Payment\PaymentGatewayCredential;
use App\Helpers\SanitizeInput;
use App\Models\CustomDomain;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\DomainReseller\Entities\DomainPaymentLog;
use Modules\DomainReseller\Http\Enums\PaymentRouteEnum;
use Modules\DomainReseller\Http\Enums\StatusEnum;
use Modules\DomainReseller\Http\Requests\CheckoutRequest;
use Modules\DomainReseller\Http\Services\DomainService;

class DomainResellerController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('domainreseller::backend.index');
    }

    public function domainList()
    {
        $domain_list = DomainPaymentLog::with('paymentable_tenant')->valid();

        if (tenant())
        {
            $domain_list = $domain_list->currentUser();
        }

        $domain_list = $domain_list->exclude(['contact_billing', 'contact_registrant', 'contact_tech', 'unique_key', 'track'])->get();

        return view('domainreseller::backend.domain-list', compact('domain_list'));
    }

    public function failedDomainList()
    {
        $domain_list = DomainPaymentLog::with('paymentable_tenant')->inValid();

        if (tenant())
        {
            $domain_list = $domain_list->currentUser();
        }

        $domain_list = $domain_list->exclude(['contact_billing', 'contact_registrant', 'contact_tech', 'unique_key', 'track'])->get();

        return view('domainreseller::backend.domain-failed-list', compact('domain_list'));
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchDomain(Request $request)
    {
        $validated = $request->validate([
            'domain_name' => 'required'
        ]);

        $domain_name = trim(SanitizeInput::esc_html($validated['domain_name']));
        $result = (new DomainService())->search($domain_name);

        return response()->json($result);
    }

    public function selectDomain(Request $request)
    {
        $request->validate([
            'domain_name' => 'required|regex:/^[a-zA-Z0-9-]+\.[a-zA-Z]{2,}$/',
            'privacy_request' => 'required'
        ]);

        $domain_name = $request->domain_name;
        $privacy_request = $request->privacy_request;

        session()->put('cart_domain', [
            "domain_name" => $domain_name,
            "privacy_request" => $privacy_request
        ]);

        return response()->json(session()->has('cart_domain') ? [
            'status' => true,
            'url' => route('tenant.admin.domain-reseller.cart')
        ] : [
            'status' => false
        ]);
    }

    public function showCart()
    {
        $agreements = (new DomainService())->showAgreements();

        if (!empty($agreements['result'])) {
            $keys = [];
            foreach ($agreements['result'] as $item) {
                $keys[] = $item->agreementKey;
            }

            session()->put('agreementKeys', $keys);
        }

        return view('domainreseller::backend.cart', compact('agreements'));
    }

    public function showCheckout()
    {
        $session_data = session('cart_domain');
        if (empty($session_data)) {
            return to_route('tenant.admin.domain-reseller.index');
        }

        $ip = \request()->ip();

        $domainServiceObj = new DomainService();

        $countries = Cache::remember('domain-countries', 60 * 60, function () use ($domainServiceObj) {
            return $domainServiceObj->getCountries();
        });

        $result = (new DomainService())->search($session_data['domain_name']);

        $data = [];
        if ($result['status']) {
            $data = (array)$result['result'];
        }

        $cart_domain_data = $data = [
            'ip' => $ip,
            'data' => $data,
            'privacy_request' => $session_data['privacy_request'] === 'true',
            'countries' => $countries['countries'] ?? [],
            'agreement_keys' => session('agreementKeys')
        ];

        unset($cart_domain_data['countries']);
        session()->put('cart_domain_data', $cart_domain_data);

        return view('domainreseller::backend.checkout', compact('data'));
    }

    public function submitCheckout(CheckoutRequest $request, DomainService $domainServiceObj)
    {
        $validated = $request->validated();
        $purchaseValidated = $domainServiceObj->purchaseValidation($validated);

        if ($purchaseValidated['validated_result']['status']) {
            session()->put('domain_validated_data', $purchaseValidated['validated_data']);

            $cart_domain_data = session('cart_domain_data');
            $ip_address = $cart_domain_data['ip'];
            $domain_name = $cart_domain_data['data']['domain'];
            $domain_price = $cart_domain_data['data']['price'];
            $domain_period = $purchaseValidated['validated_data']['period'] ?? 1;
            $extra_fee = get_static_option_central('domain_reseller_additional_charge') ?? 0;
            $tenant = tenant();

            $payment_details = DomainPaymentLog::create([
                'user_id' => $tenant->user_id,
                'tenant_id' => $tenant->id,
                'first_name' => $validated['nameFirst'],
                'last_name' => $validated['nameLast'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'user_details' => json_encode([
                    'first_name' => $validated['nameFirst'],
                    'middle_name' => $validated['nameMiddle'],
                    'last_name' => $validated['nameLast'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'fax' => $validated['fax'],
                    'job_title' => $validated['jobTitle'],
                    'organization' => $validated['organization'],
                    'country' => $validated['country'],
                    'state' => $validated['state'],
                    'city' => $validated['city'],
                    'postal_code' => $validated['postalCode'],
                    'address1' => $validated['address1'],
                    'address2' => $validated['address2'],
                ]),
                'contact_billing' => json_encode($purchaseValidated['validated_data']['contactBilling']),
                'contact_registrant' => json_encode($purchaseValidated['validated_data']['contactRegistrant']),
                'contact_tech' => json_encode($purchaseValidated['validated_data']['contactTech']),
                'ip_address' => $ip_address,
                'domain' => $domain_name,
                'domain_price' => $domain_price,
                'extra_fee' => $extra_fee,
                'period' => $domain_period,
                'payment_gateway' => $validated['selected_payment_gateway'],
                'payment_status' => false,
                'status' => false,
                'track' => Str::random(),
                'expire_at' => now()->addYears($domain_period)
            ]);

            if((new DomainService())->isTestMode())
            {
                $domain_price = 25; // fake small amount price for test mode
            }

            $payment_gateway = $validated['selected_payment_gateway'];
            $amount_to_charge = ($domain_period * $domain_price) + $extra_fee;

            $credential_function = 'get_' . $payment_gateway . '_credential';

            if (!method_exists((new PaymentGatewayCredential()), $credential_function))
            {
                $custom_data['request'] = $request;
                $custom_data['payment_details'] = $payment_details->toArray();
                $custom_data['total'] = $amount_to_charge;

                //add extra param support to the shop checkout payment system
                $custom_data['payment_type'] = "domain-reseller";
                $custom_data['payment_for'] = "tenant";
                $custom_data['cancel_url'] = route('tenant.admin.domain-reseller.payment.cancel.static');
                $custom_data['success_url'] = route('tenant.admin.domain-reseller.payment.success', wrap_random_number($payment_details->id));
                $custom_data['type'] = 'new';

                $charge_customer_class_namespace = getChargeCustomerMethodNameByPaymentGatewayNameSpace($payment_gateway);
                $charge_customer_method_name = getChargeCustomerMethodNameByPaymentGatewayName($payment_gateway);

                $custom_charge_customer_class_object = $charge_customer_class_namespace;
                if(class_exists($charge_customer_class_namespace) && method_exists($custom_charge_customer_class_object, $charge_customer_method_name))
                {
                    try {
                        return (new $custom_charge_customer_class_object)->$charge_customer_method_name($custom_data);
                    }catch (\Exception $e){
                        return back()->with(FlashMsg::explain('danger', $e->getMessage()));
                    }
                } else {
                    return back()->with(FlashMsg::explain('danger', 'Incorrect Class or Method'));
                }
            } else {
                return PaymentGatewayCredential::$credential_function()->charge_customer(
                    self::common_charge_customer_data($amount_to_charge, $payment_details, route('landlord.admin.domain-reseller.global.ipn', $payment_gateway), 'new')
                );
            }
        }

        return back()->withInput()->withErrors(
            empty($purchaseValidated['validated_result']['fields'])
                ? $purchaseValidated['validated_result']['message']
                : data_get($purchaseValidated['validated_result'], 'fields.*.message')
        );
    }

    public function renewPage($domain_id)
    {
        abort_if(empty($domain_id), 404);
        $domain_id = unwrap_random_number($domain_id);

        $order_details = DomainPaymentLog::currentUser()->where('id', $domain_id)->firstOrFail();


        return view('domainreseller::backend.renew', compact('order_details'));
    }

    public function renewCheckout($id, Request $request)
    {
        abort_if(empty($id), 404);

        $validated = $request->validate([
            'period' => 'required|integer',
            'selected_payment_gateway' => 'required'
        ]);

        $domain_id = unwrap_random_number($id);
        $order_details = DomainPaymentLog::currentUser()->where('id', $domain_id)->firstOrFail();

        $domain_period = $validated['period'] ?? 1;

        $old_expire_date = $order_details->expire_at;
        if ($old_expire_date < now())
        {
            $new_expire_date = now()->addYears($domain_period);
        } else {
            $new_expire_date = Carbon::parse($old_expire_date)->addYears($domain_period);
        }

        // checking if period is greater than 10 year
        $periodValidation = $this->validatePeriod($new_expire_date);
        if ($periodValidation['status'])
        {
            return back()->with($periodValidation['response']);
        }

        $order_details->update([
            'period' => $domain_period,
            'expire_at' => $new_expire_date,
            'payment_status' => StatusEnum::INACTIVE,
            'status' => StatusEnum::INACTIVE,
            'payment_gateway' => $validated['selected_payment_gateway']
        ]);

        $domain_price = $order_details->domain_price;
        if((new DomainService())->isTestMode())
        {
            $domain_price = 25; // fake small amount price for test mode
        }
        $extra_fee = get_static_option_central('domain_reseller_additional_charge') ?? 0;
        $amount_to_charge = ($domain_period * $domain_price) + $extra_fee;

        $payment_gateway = $validated['selected_payment_gateway'];
        $credential_function = 'get_' . $payment_gateway . '_credential';

        if (!method_exists((new PaymentGatewayCredential()), $credential_function))
        {
            $custom_data['request'] = $request;
            $custom_data['payment_details'] = $order_details->toArray();
            $custom_data['total'] = $amount_to_charge;

            //add extra param support to the shop checkout payment system
            $custom_data['payment_type'] = "domain-reseller";
            $custom_data['payment_for'] = "tenant";
            $custom_data['cancel_url'] = route('tenant.admin.domain-reseller.payment.cancel.static');
            $custom_data['success_url'] = route('tenant.admin.domain-reseller.payment.success', wrap_random_number($order_details->id));
            $custom_data['type'] = 'renew';

            $charge_customer_class_namespace = getChargeCustomerMethodNameByPaymentGatewayNameSpace($payment_gateway);
            $charge_customer_method_name = getChargeCustomerMethodNameByPaymentGatewayName($payment_gateway);

            $custom_charge_customer_class_object = $charge_customer_class_namespace;
            if(class_exists($charge_customer_class_namespace) && method_exists($custom_charge_customer_class_object, $charge_customer_method_name))
            {
                try {
                    return (new $custom_charge_customer_class_object)->$charge_customer_method_name($custom_data);
                }catch (\Exception $e){
                    return back()->with(FlashMsg::explain('danger', $e->getMessage()));
                }
            } else {
                return back()->with(FlashMsg::explain('danger', 'Incorrect Class or Method'));
            }
        } else {
            return PaymentGatewayCredential::$credential_function()->charge_customer(
                self::common_charge_customer_data($amount_to_charge, $order_details, route('landlord.admin.domain-reseller.global.ipn', $payment_gateway), 'renew')
            );
        }
    }

    private static function common_charge_customer_data($amount_to_charge, $payment_details, $ipn_url, $type): array
    {
        session()->put('purchase-option', $type);
        $purchase_details = "Payment For Order ID: #{$payment_details->id}" . PHP_EOL .
            "Payer Name: {$payment_details->first_name}" . PHP_EOL .
            "Payer Email: {$payment_details->email}" . PHP_EOL .
            "Payer Order: {$payment_details->domain}";

        return [
            'amount' => "$amount_to_charge",
            'title' => "Order ID: {$payment_details->id}",
            'description' => $purchase_details,
            'order_id' => $payment_details->id,
            'track' => $payment_details->track,
            'cancel_url' => route(PaymentRouteEnum::CANCEL_ROUTE, wrap_random_number($payment_details->id)),
            'success_url' => route(PaymentRouteEnum::SUCCESS_ROUTE, wrap_random_number($payment_details->id)),
            'email' => $payment_details->email,
            'name' => $payment_details->first_name,
            'payment_type' => 'order',
            'ipn_url' => $ipn_url,
        ];
    }

    public function failedPurchaseAction($id)
    {
        abort_if(empty($id), 404);
        $extracted_id = unwrap_random_number($id);

        $order_details = DomainPaymentLog::find($extracted_id);
        $is_renew = $order_details->purchase_count > 1;

        $response_message = [
            'status' => false,
            'type' => 'danger',
            'message' => __('Something went wrong')
        ];

        if ($is_renew)
        {
            $renew_body = ["period" => $order_details->period];
            $result = (new DomainService())->renewDomain($order_details->domain, $renew_body);

            if ($result['status'])
            {
                $order_details->update([
                    'status' => StatusEnum::ACTIVE
                ]);

                $response_message = [
                    'status' => true,
                    'type' => 'success',
                    'message' => __("the domain {$order_details->domain} is renewed successfully.")
                ];
            } else {
                if ($result['code'] === 404)
                {
                    $response_message = [
                        'status' => false,
                        'type' => 'danger',
                        'message' => __('The service is not available in your region or domain does not exist.')
                    ];
                }

                if ($result['code'] === 422)
                {
                    $response_message = [
                        'status' => false,
                        'type' => 'danger',
                        'message' => __('The domain period can not be more than 10 years.')
                    ];
                }
            }
        } else {
            $user_details = (array) json_decode($order_details->user_details);

            $purchase_body = [
                "consent" => [
                    "agreedAt" => $order_details->created_at->format('Y-m-d\TH:i:s\Z'),
                    "agreedBy" => $order_details->ip_address,
                    "agreementKeys" => ["DNRA"],
                ],
                "contactAdmin" => [
                    "addressMailing" => [
                        "country" => $user_details['country'],
                        "state" => $user_details['state'],
                        "city" => $user_details['city'],
                        "postalCode" => $user_details['postal_code'],
                        "address1" => $user_details['address1'],
                        "address2" => $user_details['address2'],
                    ],
                    "nameFirst" => $user_details['first_name'],
                    "nameLast" => $user_details['last_name'],
                    "nameMiddle" => $user_details['middle_name'],
                    "email" => $user_details['email'],
                    "phone" => $user_details['phone'],
                    "fax" => $user_details['fax'],
                    "jobTitle" => $user_details['job_title'],
                    "organization" => $user_details['organization']
                ],
                "contactBilling" => (array) json_decode($order_details->contact_billing),
                "contactRegistrant" => (array) json_decode($order_details->contact_registrant),
                "contactTech" => (array) json_decode($order_details->contact_tech),
                "domain" => $order_details->domain,
                "nameServers" => (new DomainService())->currentProvider()['nameservers'],
                "period" => (int)$order_details->period,
                "privacy" => false,
                "renewAuto" => false,
            ];

            $result = (new DomainService())->purchaseDomain($purchase_body);

            if ($result['status'])
            {
                $order_details->update([
                    'status' => StatusEnum::ACTIVE
                ]);

                $response_message = [
                    'status' => true,
                    'type' => 'success',
                    'message' => __("the domain {$order_details->domain} is purchased successfully.")
                ];
            } else {
                if ($result['code'])
                {
                    $response_message = [
                        'status' => false,
                        'type' => 'danger',
                        'message' => str_replace(['`'],'',$result['message'])
                    ];
                }
            }
        }

        return back()->with(FlashMsg::explain($response_message['type'], $response_message['message']));
    }

    public function activateCustomDomain()
    {
        $id = \request()->id;
        if (empty($id)) {
            return response()->json([
                'status' => false,
                'type' => 'danger',
                'msg' => __('Resource not found')
            ]);
        }

        $current_tenant = tenant();
        $current_user_id = $current_tenant->user_id;

        $domain_details = DomainPaymentLog::where('id', $id)
            ->where(['user_id' => $current_user_id, 'tenant_id' => $current_tenant->id])
            ->valid()
            ->exclude(['contact_billing', 'contact_registrant', 'contact_tech'])
            ->first();

        $root_domain_a_record = get_static_option_central('server_ip') ?? $_SERVER['SERVER_ADDR'];
        $dns_body = [
            "data" => (string)$root_domain_a_record,
            "ttl" => 600
        ];

        $response = [];
        try {
            $recorded = (new DomainService())->setDNSRecord($domain_details->domain, [$dns_body]);
            if ($recorded['status'])
            {
                $updated_instance = CustomDomain::updateOrCreate(
                    [
                        'user_id' => $current_user_id,
                        'old_domain' => $current_tenant->id
                    ],
                    [
                        'old_domain' => $current_tenant->id,
                        'custom_domain' => $domain_details->domain,
                        'custom_domain_status' => 'connected',
                    ]
                );

                $updated_instance->tenant->domains()->update(['domain' => $updated_instance->custom_domain]);
                $updated_instance->touch('updated_at');

                $response = [
                    'status' => true,
                    'type' => 'success',
                    'msg' => __('Your custom domain is connected')
                ];
            }
        } catch (\Exception $exception) {
            $response = [
                'status' => false,
                'type' => 'danger',
                'msg' => __('Something went wrong')
            ];
        }

        return response()->json($response);
    }

    public function getStates()
    {
        $countryKey = \request()->countryKey;
        $states = (new DomainService())->getStates($countryKey);
        return response()->json($states);
    }

    public function settings()
    {
        abort_if(!empty(tenant()), 403);
        $providers = DomainService::providers();
        return view('domainreseller::backend.settings', compact('providers'));
    }

    public function UpdateSettings(Request $request)
    {
        $validated = $request->validate([
            'godaddy_api_key' => 'required_if:godaddy_api_key,godaddy',
            'godaddy_api_secret' => 'required_if:godaddy_api_secret,godaddy',
            'godaddy_api_app_name' => 'nullable'
        ]);

        foreach ($validated ?? [] as $index => $item) {
            update_static_option_central($index, SanitizeInput::esc_html($item));
        }

        return back()->with(FlashMsg::settings_update('Domain reseller settings updated'));
    }

    public function updateAdditionalSettings(Request $request)
    {
        $validated = $request->validate([
            'additional_charge' => 'nullable|numeric|min:0',
            'additional_fee_title' => 'nullable'
        ]);

        update_static_option_central('domain_reseller_additional_charge', SanitizeInput::esc_html(trim($validated['additional_charge'])));
        update_static_option_central('domain_reseller_additional_fee_title', SanitizeInput::esc_html(trim($validated['additional_fee_title'])));

        return back()->with(FlashMsg::settings_update('Domain reseller additional charge updated'));
    }

    public function UpdateConfiguration(Request $request)
    {
        $rules = [
            'godaddy_environment' => 'nullable'
        ];

        $request->validate($rules);

        foreach ($rules ?? [] as $index => $item) {
            update_static_option_central($index, empty(SanitizeInput::esc_html($request->$index)) ? null : trim(SanitizeInput::esc_html($request->$index)));
        }

        return back()->with(FlashMsg::settings_update('Domain reseller config settings updated'));
    }

    public function changeStatus()
    {
        $request = \request();
        $this->validation($request);

        $active_domain_provider_name = 'active_domain_provider';

        if (empty(get_static_option_central($active_domain_provider_name))) {
            update_static_option_central($active_domain_provider_name, SanitizeInput::esc_html($request->option));
        } else {
            if (get_static_option_central($active_domain_provider_name) == SanitizeInput::esc_html($request->option)) {
                delete_static_option_central($active_domain_provider_name);
            } else {
                delete_static_option_central($active_domain_provider_name);
                update_static_option_central($active_domain_provider_name, SanitizeInput::esc_html($request->option));
            }
        }

        return response()->json([
            'type' => "success"
        ]);
    }

    private function validatePeriod($new_expire_date)
    {
        $months = now()->diffInMonths($new_expire_date);
        if ($months >= (10*12))
        {
            return [
                'status' => true,
                'response' => FlashMsg::explain('danger', __('The domain period can not be more than 10 years'))
            ];
        }

        return [
            'status' => false
        ];
    }

    private function validation($request)
    {
        abort_if(!$request->has('option'), 404);
        abort_if(empty($request->option), 404);

        $gateway_slugs_array = data_get(DomainService::providers(), '*.slug');
        abort_if(!in_array($request->option, $gateway_slugs_array), 404);
    }

    private function validateDomain($text): array
    {
        return [
            'rule' => preg_match('/^[a-zA-Z0-9-]+\.[a-zA-Z]{2,}$/', $text),
            'msg' => __('The domain must be a valid domain')
        ];
    }
}
