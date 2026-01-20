<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\TenantNotificationEvent;
use App\Helpers\GenerateTenantToken;
use App\Models\PaymentLogs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Contracts\TenantWithDatabase;

class TenantInformationUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var TenantWithDatabase */
    protected $tenant;

    public function __construct(TenantWithDatabase $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
       $paymentLog =  PaymentLogs::where('tenant_id',$this->tenant->getTenantKey())->latest()->first();

         if (is_null($paymentLog))
         {
              return;
         }

        $package = $paymentLog->package;
        $package_start_date = '';
        $package_expire_date =  '';
        if(!empty($package)){

            if($package->type == 0){ //monthly
                $package_start_date = Carbon::now()->format('d-m-Y h:i:s');
                $package_expire_date = Carbon::now()->addMonth()->format('d-m-Y h:i:s');

            }elseif ($package->type == 1){ //yearly
                $package_start_date = Carbon::now()->format('d-m-Y h:i:s');
                $package_expire_date = Carbon::now()->addYear()->format('d-m-Y h:i:s');
            }else{ //lifetime
                $package_start_date = Carbon::now()->format('d-m-Y h:i:s');
                $package_expire_date = null;
            }
        }

        $paymentLog->status = $paymentLog->status  == 'trial' ? 'trial' : 'complete';
        $paymentLog->payment_status = $paymentLog->status  == 'trial' ? 'pending' : 'complete';
        $paymentLog->save();

         DB::table('tenants')->where('id',$this->tenant->getTenantKey())->update([
             'start_date' => $package_start_date,
             'expire_date' => $package_expire_date,
             'user_id' => $paymentLog->user_id,
             'theme_slug' => $paymentLog->theme,
             'unique_key' => GenerateTenantToken::token(),
             'cleanup' => 1,
         ]);


        $event_data = ['id' => $paymentLog->id, 'title' => __('New subscription plan taken'), 'type' => 'new_subscription',];
        event(new TenantNotificationEvent($event_data));
    }
}
