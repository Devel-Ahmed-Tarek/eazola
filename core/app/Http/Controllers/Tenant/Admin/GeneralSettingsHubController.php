<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

class GeneralSettingsHubController extends Controller
{
    public function index()
    {
        $enabledGateways = [];

        $current_tenant_payment_data = tenant()->payment_log()?->first() ?? null;
        $package = $current_tenant_payment_data->package ?? [];
        $all_features = $package->plan_features ?? [];
        $featureNames = method_exists($all_features, 'pluck')
            ? $all_features->pluck('feature_name')->toArray()
            : [];

        $gatewaysMap = [
            'currency' => [
                'route' => 'tenant.admin.payment.currency.settings',
                'label' => __('Currencies'),
            ],
            'paypal' => [
                'route' => 'tenant.admin.payment.paypal.settings',
                'label' => __('Paypal'),
            ],
            'paytm' => [
                'route' => 'tenant.admin.payment.paytm.settings',
                'label' => __('Paytm'),
            ],
            'stripe' => [
                'route' => 'tenant.admin.payment.stripe.settings',
                'label' => __('Stripe'),
            ],
            'razorpay' => [
                'route' => 'tenant.admin.payment.razorpay.settings',
                'label' => __('Razorpay'),
            ],
            'paystack' => [
                'route' => 'tenant.admin.payment.paystack.settings',
                'label' => __('Paystack'),
            ],
            'mollie' => [
                'route' => 'tenant.admin.payment.mollie.settings',
                'label' => __('Mollie'),
            ],
            'payfast' => [
                'route' => 'tenant.admin.payment.payfast.settings',
                'label' => __('Payfast'),
            ],
            'midtrans' => [
                'route' => 'tenant.admin.payment.midtrans.settings',
                'label' => __('Midtrans'),
            ],
            'cashfree' => [
                'route' => 'tenant.admin.payment.cashfree.settings',
                'label' => __('Cashfree'),
            ],
            'instamojo' => [
                'route' => 'tenant.admin.payment.instamojo.settings',
                'label' => __('Instamojo'),
            ],
            'marcadopago' => [
                'route' => 'tenant.admin.payment.marcadopago.settings',
                'label' => __('Marcadopago'),
            ],
            'zitopay' => [
                'route' => 'tenant.admin.payment.zitopay.settings',
                'label' => __('Zitopay'),
            ],
            'squareup' => [
                'route' => 'tenant.admin.payment.squareup.settings',
                'label' => __('Squareup'),
            ],
            'cinetpay' => [
                'route' => 'tenant.admin.payment.cinetpay.settings',
                'label' => __('Cinetpay'),
            ],
            'paytabs' => [
                'route' => 'tenant.admin.payment.paytabs.settings',
                'label' => __('Paytabs'),
            ],
            'billplz' => [
                'route' => 'tenant.admin.payment.billplz.settings',
                'label' => __('Billplz'),
            ],
            'bank_transfer' => [
                'route' => 'tenant.admin.payment.bank_transfer.settings',
                'label' => __('Bank Transfer'),
            ],
            'manual_payment' => [
                'route' => 'tenant.admin.payment.manual_payment.settings',
                'label' => __('Manual Payment'),
            ],
            'flutterwave' => [
                'route' => 'tenant.admin.payment.flutterwave.settings',
                'label' => __('Flutterwave'),
            ],
            'toyyibpay' => [
                'route' => 'tenant.admin.payment.toyyibpay.settings',
                'label' => __('Toyyibpay'),
            ],
            'pagali' => [
                'route' => 'tenant.admin.payment.pagali.settings',
                'label' => __('Pagali'),
            ],
            'authorizenet' => [
                'route' => 'tenant.admin.payment.authorizenet.settings',
                'label' => __('Authorizenet'),
            ],
            'sitesway' => [
                'route' => 'tenant.admin.payment.sitesway.settings',
                'label' => __('Sitesway'),
            ],
            'kinetic' => [
                'route' => 'tenant.admin.payment.kinetic.settings',
                'label' => __('Kinetic'),
            ],
            'paymob' => [
                'route' => 'tenant.admin.payment.paymob.settings',
                'label' => __('Paymob'),
            ],
            'awdpay' => [
                'route' => 'tenant.admin.payment.awdpay.settings',
                'label' => __('Awdpay'),
            ],
            'powertranzpay' => [
                'route' => 'tenant.admin.payment.powertranzpay.settings',
                'label' => __('Powertranzpay'),
            ],
        ];

        foreach ($gatewaysMap as $featureKey => $meta) {
            // currency is always available as long as route exists
            if ($featureKey !== 'currency' && !in_array($featureKey, $featureNames, true)) {
                continue;
            }
            if (!Route::has($meta['route'])) {
                continue;
            }
            $enabledGateways[] = $meta;
        }

        return view('tenant.admin.general-settings-hub.index', [
            'enabledGateways' => $enabledGateways,
        ]);
    }
}

