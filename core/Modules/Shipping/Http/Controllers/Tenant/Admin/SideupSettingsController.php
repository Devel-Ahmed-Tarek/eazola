<?php

namespace Modules\Shipping\Http\Controllers\Tenant\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Modules\Shipping\Entities\ShippingAccount;
use Modules\Shipping\Http\Services\Sideup\SideupClient;
use App\Helpers\FlashMsg;

class SideupSettingsController extends Controller
{
    /** طريقة الربط: api_key أو email_password (Legacy عبر إيميل + باسورد أو ملف JSON) */
    public const AUTH_API_KEY = 'api_key';
    public const AUTH_EMAIL_PASSWORD = 'email_password';

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
            'auth_type'   => 'nullable|in:api_key,email_password',
            'api_key'     => 'nullable|string',
            'base_url'    => 'nullable|url',
            'email'       => 'nullable|email',
            'password'    => 'nullable|string',
            'enabled'     => 'nullable|string',
            'legacy_json' => 'nullable|file|mimes:json',
        ]);

        $account = ShippingAccount::firstOrNew(['provider' => 'sideup']);
        $account->base_url = $data['base_url'] ?? $account->base_url;
        $account->enabled = !empty($data['enabled']);

        $meta = $account->meta ?? [];
        $meta['auth_type'] = $data['auth_type'] ?? self::AUTH_API_KEY;

        // تحميل من ملف Legacy JSON (إيميل + باسورد + اختياري base_url)
        if ($request->hasFile('legacy_json')) {
            try {
                $json = json_decode(file_get_contents($request->file('legacy_json')->getRealPath()), true);
                if (is_array($json)) {
                    if (!empty($json['email'])) {
                        $meta['email'] = $json['email'];
                    }
                    if (!empty($json['password'])) {
                        $meta['password'] = Crypt::encryptString($json['password']);
                    }
                    if (!empty($json['base_url'])) {
                        $account->base_url = $json['base_url'];
                    }
                    $meta['auth_type'] = self::AUTH_EMAIL_PASSWORD;
                }
            } catch (\Throwable $e) {
                Log::warning('SideUp legacy JSON parse failed', ['error' => $e->getMessage()]);
                return back()->with(FlashMsg::explain('danger', __('Invalid legacy JSON file.') . ' ' . $e->getMessage()));
            }
        } else {
            if (($data['auth_type'] ?? '') === self::AUTH_EMAIL_PASSWORD) {
                if (!empty($data['email'])) {
                    $meta['email'] = $data['email'];
                }
                if (!empty($data['password'])) {
                    $meta['password'] = Crypt::encryptString($data['password']);
                }
            }
        }

        if (($meta['auth_type'] ?? '') === self::AUTH_API_KEY) {
            $account->api_key = $data['api_key'] ?? null;
        }
        $account->meta = $meta;
        $account->save();

        return back()->with(FlashMsg::create_succeed(__('SideUp settings updated')));
    }

    public function test(Request $request)
    {
        $account = ShippingAccount::where('provider', 'sideup')->first();

        if (empty($account?->base_url)) {
            return back()->with(FlashMsg::explain('danger', __('Please save Base URL first.')));
        }

        try {
            $client = SideupClient::fromAccount($account);
            if (!$client) {
                return back()->with(FlashMsg::explain('danger', __('Set either API Key or Email + Password (or upload legacy JSON).')));
            }
            $client->getShipment('ping-test');
        } catch (\Throwable $e) {
            return back()->with(FlashMsg::explain('danger', __('Connection failed: ') . $e->getMessage()));
        }

        return back()->with(FlashMsg::create_succeed(__('SideUp connection looks good.')));
    }
}

