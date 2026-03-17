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

