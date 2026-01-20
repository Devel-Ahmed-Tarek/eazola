<?php

namespace App\Helpers;

use App\Models\Tenant;
use App\Models\TenantUniqueKey;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Str;

class GenerateTenantToken
{
    public static function regenerate(Tenant $tenant)
    {
        $token_created_time = $tenant->tenant_unique_key?->updated_at;
        if (!empty($token_created_time)) {
            $time_difference = $token_created_time->diffInMinutes(now());

            if ($time_difference >= 5) {
                $tokenize_tenant = TenantUniqueKey::where('tenant_id',$tenant->id)->first();
                self::generate($tokenize_tenant->tenant);
            }
        } else {
            self::generate($tenant);
        }
    }

    public static function generate(Tenant $tenant)
    {
        $token = self::token();
        try {
            DB::table('tenants')->find($tenant->id)->update([
                'unique_key' => $token
            ]);

            TenantUniqueKey::updateOrCreate(
                [
                    'tenant_id' => $tenant->id
                ],
                [
                    'tenant_id' => $tenant->id,
                    'unique_key' => $token,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
        } catch (\Exception $exception) {
        }
    }

    public static function token()
    {
        return Hash::make(Str::random(32));
    }
}
