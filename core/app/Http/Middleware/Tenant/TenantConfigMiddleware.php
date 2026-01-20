<?php

namespace App\Http\Middleware\Tenant;

use App\Models\CustomDomain;
use App\Models\StaticOption;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class TenantConfigMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function __construct()
    {
        $this->setDrivers();

        if ((!moduleExists('CloudStorage') || !isPluginActive('CloudStorage')))
        {
            if (tenant())
            {
                Config::set('filesystems.default', 'TenantMediaUploader');
            } else {
                Config::set('filesystems.default', 'LandlordMediaUploader');
            }
        }
    }

    public function handle(Request $request, Closure $next)
    {
        // switches timezone according to tenant timezone from database value
        if (tenant()){
            $smtp_settings_values = StaticOption::select(['option_name','option_value'])->whereIn('option_name',[
                'site_smtp_driver',
                'site_smtp_host',
                'site_smtp_port',
                'site_smtp_username',
                'site_smtp_password',
                'site_smtp_encryption',
                'site_global_email'
            ])->get()->pluck('option_value','option_name')->toArray();

            Config::set('mail.mailers', $smtp_settings_values['site_smtp_driver'] ?? Config::get('mail.mailers'));
            $mailers = !empty($smtp_settings_values) ? $smtp_settings_values['site_smtp_driver'] : (get_static_option_central('site_smtp_driver') ?? 'smtp');

            Config::set([
                "mail.mailers.{$mailers}.transport" => $smtp_settings_values['site_smtp_driver'] ?? Config::get('mail.mailers.smtp.transport'),
                "mail.mailers.{$mailers}.host" => $smtp_settings_values['site_smtp_host'] ?? Config::get('mail.mailers.smtp.host'),
                "mail.mailers.{$mailers}.port" => $smtp_settings_values['site_smtp_port'] ?? Config::get('mail.mailers.smtp.port'),
                "mail.mailers.{$mailers}.username" => $smtp_settings_values['site_smtp_username'] ?? Config::get('mail.mailers.smtp.username'),
                "mail.mailers.{$mailers}.password" => $smtp_settings_values['site_smtp_password'] ?? Config::get('mail.mailers.smtp.password'),
                "mail.mailers.{$mailers}.encryption" => $smtp_settings_values['site_smtp_encryption'] ?? Config::get('mail.mailers.smtp.encryption'),
                "mail.mailers.{$mailers}.timeout" => null,
                "mail.mailers.{$mailers}.auth_mode" => null,
                "mail.mailers.{$mailers}.verify_peer" => false,
                "mail.mailers.{$mailers}.verify_peer_name" => false,
                "mail.mailers.{$mailers}.allow_self_signed" => true,
                "mail.from.address" => $smtp_settings_values['site_global_email'] ?? Config::get('mail.from.address')
            ]);

            //todo change booted config file on the fly
            $timezone = \Cache::remember('tenant_timezone', 60*60*24, function () {
                return get_static_option('timezone');
            });
            \Config::set('app.timezone', $timezone);

            // storage management
            $storagePathFix = str_replace('tenant'.tenant()->getTenantKey(),'', storage_path('../../assets/tenant/uploads/media-uploader/'));
            Config::set('filesystems.disks.TenantMediaUploader.root',$storagePathFix.tenant()->getTenantKey());
            $storage_driver = get_static_option_central('storage_driver','TenantMediaUploader');
            $defaultStorage = is_null($storage_driver) ? "TenantMediaUploader" : $storage_driver;
            $defaultStorage = $defaultStorage == 'LandlordMediaUploader' ? 'TenantMediaUploader' : $defaultStorage;
            Config::set('filesystems.default', $defaultStorage);
        }
        else
        {
            Config::set('filesystems.default', get_static_option_central('storage_driver','LandlordMediaUploader'));
        }

        return $next($request);
    }

    private function setDrivers(): void
    {
        $driver = get_static_option_central('storage_driver', tenant() ? 'TenantMediaUploader' : 'LandlordMediaUploader');

        if (in_array($driver, ['wasabi', 's3', 'cloudFlareR2']))
        {
            $db_name = match ($driver)
            {
                "wasabi" => "wasabi",
                "s3" => "aws",
                "cloudFlareR2" => "cloudflare_r2"
            };

            Config::set([
                "filesystems.disks.{$driver}.key" => get_static_option_central("{$db_name}_access_key_id") ?? Config::get("filesystems.disks.{$driver}.key"),
                "filesystems.disks.{$driver}.secret" => get_static_option_central("{$db_name}_secret_access_key") ?? Config::get("filesystems.disks.{$driver}.secret"),
                "filesystems.disks.{$driver}.region" => get_static_option_central("{$db_name}_default_region") ?? Config::get("filesystems.disks.{$driver}.region"),
                "filesystems.disks.{$driver}.bucket" => get_static_option_central("{$db_name}_bucket") ?? Config::get("filesystems.disks.{$driver}.bucket"),
                "filesystems.disks.{$driver}.endpoint" => get_static_option_central("{$db_name}_endpoint") ?? Config::get("filesystems.disks.{$driver}.endpoint"),
            ]);

            if (in_array($driver, ['s3', 'cloudFlareR2']))
            {
                Config::set([
                    "filesystems.disks.{$driver}.url" => get_static_option_central("{$db_name}_url") ?? Config::get("filesystems.disks.{$driver}.url"),
                    "filesystems.disks.{$driver}.use_path_style_endpoint" => true
                ]);
            }
        }
    }
}
