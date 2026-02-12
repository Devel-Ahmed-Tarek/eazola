<?php

namespace Modules\Shipping\Http\Controllers\Tenant\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Shipping\Entities\ShippingAccount;
use Modules\Shipping\Http\Services\Sideup\SideupClient;
use App\Helpers\FlashMsg;

class SideupSettingsController extends Controller
{
    public function edit()
    {
        $account = ShippingAccount::where('provider', 'sideup')->first();

        return view('shipping::tenant.admin.sideup-settings', [
            'account' => $account,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'api_key'  => 'nullable|string',
            'base_url' => 'nullable|url',
            'enabled'  => 'nullable|string',
        ]);

        $account = ShippingAccount::firstOrNew(['provider' => 'sideup']);
        $account->api_key = $data['api_key'] ?? null;
        $account->base_url = $data['base_url'] ?? null;
        $account->enabled = !empty($data['enabled']);
        $account->save();

        return back()->with(FlashMsg::create_succeed(__('SideUp settings updated')));
    }

    public function test(Request $request)
    {
        $account = ShippingAccount::where('provider', 'sideup')->first();

        if (empty($account?->api_key) || empty($account?->base_url)) {
            return back()->with(FlashMsg::explain('danger', __('Please save API Key and Base URL first.')));
        }

        try {
            $client = new SideupClient($account->base_url, $account->api_key);
            // simple ping – لو فيه endpoint مخصص نقدر نعدله هنا بعد مراجعة الـ JSON
            $client->getShipment('ping-test'); // هتترمي لو الـ endpoint مش موجود – مجرد smoke test
        } catch (\Throwable $e) {
            return back()->with(FlashMsg::explain('danger', __('Connection failed: ') . $e->getMessage()));
        }

        return back()->with(FlashMsg::create_succeed(__('SideUp connection looks good.')));
    }
}

