<?php

namespace App\Traits\SeederMethods;

use App\Helpers\FlashMsg;
use App\Helpers\ResponseMessage;
use App\Helpers\SeederHelpers\JsonDataModifier;
use App\Models\Language;
use App\Models\StaticOption;
use Illuminate\Http\Request;

trait TenantDemoDataSeederMethod
{

    public function tenant_demo_data_settings(Request $request)
    {
        $only_path = 'assets/tenant/page-layout/tenant-demo-data.json';
        if (!file_exists($only_path) && !is_dir($only_path)) {
            $data = [
                "site_es_CO_title" => "site title",
                "site_es_CO_footer_copyright_text" => null,
                "site_en_US_title" => "site title",
                "site_en_US_footer_copyright_text" => null,
                "site_ar_title" => "\u0627\u0644\u0639\u0646\u0648\u0627\u0646 \u0627\u0644\u0645\u0648\u0642\u0639\u064a",
                "site_ar_footer_copyright_text" => "\u0627\u0644\u0639\u0646\u0648\u0627\u0646 \u0627\u0644\u0645\u0648\u0642\u0639\u064a",
                "site_bn_BD_title" => null,
                "site_bn_BD_footer_copyright_text" => null,
                "site_third_party_tracking_code_just_after_head" => null,
                "site_third_party_tracking_code" => null,
                "site_third_party_tracking_code_just_after_body" => null,
                "site_third_party_tracking_code_just_before_body_close" => null,
                "site_google_analytics" => null,
                "site_google_captcha_v3_site_key" => null,
                "site_google_captcha_v3_secret_key" => null,
                "social_facebook_status" => "on",
                "facebook_client_id" => null,
                "facebook_client_secret" => null,
                "social_google_status" => "on",
                "google_client_id" => null,
                "google_client_secret" => "D5J14HZQjxefNuKKRuHPfF42",
                "google_adsense_publisher_id" => null,
                "google_adsense_customer_id" => null
            ];

            $json_data = json_encode($data);
            file_put_contents($only_path, $json_data);
        }

        $tenant_demo_data = file_get_contents($only_path);
        $all_data_decoded = json_decode($tenant_demo_data);

        return view(self::BASE_PATH . 'tenant-demo-data/tenant-demo-data-setting', compact('all_data_decoded' ));
    }


    public function update_tenant_demo_data_settings(Request $request)
    {
        $data = $request->all();
        unset($data["_token"]);

        file_put_contents('assets/tenant/page-layout/tenant-demo-data.json', json_encode($data));


        $this->validate($request, [
            'tawk_api_key' => 'nullable|string',
            'google_adsense_id' => 'nullable|string',
            'site_third_party_tracking_code' => 'nullable|string',
            'site_google_analytics' => 'nullable|string',
            'site_google_captcha_v3_secret_key' => 'nullable|string',
            'site_google_captcha_v3_site_key' => 'nullable|string',
        ]);

        $fields = [
            'site_google_captcha_v3_secret_key',
            'site_google_captcha_v3_site_key',
            'site_third_party_tracking_code',
            'site_google_analytics',

            'social_facebook_status',
            'social_google_status',
            'google_client_id',
            'google_client_secret',
            'facebook_client_id',
            'facebook_client_secret',

            'site_third_party_tracking_code_just_after_head',
            'site_third_party_tracking_code_just_after_body',
            'site_third_party_tracking_code_just_before_body_close',

            'google_adsense_publisher_id',
            'google_adsense_customer_id',

        ];

        foreach ($fields as $field){
            update_static_option($field,$request->$field);
        }

        foreach (Language::all() as $lang){
            $fields = [
                'site_'.$lang->slug.'_title'  => 'nullable|string',
                'site_'.$lang->slug.'_tag_line' => 'nullable|string',
                'site_'.$lang->slug.'_footer_copyright_text' => 'nullable|string',
            ];

            foreach ($fields as $field_name => $rules){
                update_static_option($field_name,$request->$field_name);
            }
        }


        return redirect()->back()->with([
            'msg' => __('tenant demo data changed'),
            'type' => 'success'
        ]);
    }

}
