<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\CacheKeyEnums;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Stancl\Tenancy\Contracts\TenantWithDatabase;

class TenantFileSycnForNewTenant implements ShouldQueue
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
        /* file sync test */
        $allFiles = Cache::remember(CacheKeyEnums::ALL_AWS_S3_DEMO_IMAGES_FILES->value, 300 * 60,function (){
            return  Storage::allFiles('/seeder-files/all-media');
        });

        $tenantKey = $this->tenant->id;
        //todo get folder name
        foreach ($allFiles as $file){
            TenanFileCopyFromCloudForNewTenant::dispatch($file,$tenantKey)->onConnection('tenant_file_sync')->delay(Carbon::now()->addSeconds(2));
        }
    }

}
