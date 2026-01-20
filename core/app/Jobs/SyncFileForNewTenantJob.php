<?php

namespace App\Jobs;

use App\Models\MediaUploader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Events\DatabaseDeleted;
use Stancl\Tenancy\Events\DeletingDatabase;

class SyncFileForNewTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tenant;

    public function __construct(public $file,public $tenant_id)
    {
        //https://r2bucket.ditopic.store/grid/grid-t-abd-21692807176.jpg?X-Amz-Content-Sha256=UNSIGNED-PAYLOAD&X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=6542b6133899189be81418ce72055070%2F20230823%2Fus-east-1%2Fs3%2Faws4_request&X-Amz-Date=20230823T161324Z&X-Amz-SignedHeaders=host&X-Amz-Expires=1200&X-Amz-Signature=fc3c7dea27ae85f5d75c3446036917a04adb0d8caacf9fff6f8002e1bc6dc407
    }

    public function handle()
    {
        $files = ["grid/",'large/','thumb/','tiny/',''];
        $file_path = $this->file;
        $tenant_id = $this->tenant_id;
        $file_instance = new \Illuminate\Http\File($file_path);

        foreach($files as $vFile){
            $prefix = '';
            if ($vFile != ''){
                $prefix = str_replace('/','',$vFile).'-';
            }

            $local_file_path = base_path("../assets/tenant/seeder-files/all-media/".$vFile.$prefix.$file_instance->getFilename());
            $cl_file_path = $tenant_id."/".$vFile.$prefix.$file_instance->getFilename();
            // // /* checking the file exits in locally or not, if not exits return this jobs. */

            if (!file_exists($local_file_path)){
                return;
            }
            // // //todo:: check the file already exists in the cloud if not exits then create then copy that file to cloud
//            if (!Storage::drive("cloudFlareR2")->exists($cl_file_path)){
                $fileNeed =  new \Illuminate\Http\File($local_file_path);
                //todo: have to check for three file

                Storage::putFileAs("/".$tenant_id."/".$vFile,$fileNeed,$prefix.$file_instance->getFilename());
//            }
        }
    }

}
