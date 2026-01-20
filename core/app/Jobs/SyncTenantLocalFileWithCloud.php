<?php

namespace App\Jobs;

use App\Models\MediaUploader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Events\DatabaseDeleted;
use Stancl\Tenancy\Events\DeletingDatabase;
use Stancl\Tenancy\Facades\Tenancy;

class SyncTenantLocalFileWithCloud implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tenant;

    public function __construct(public $item,public $tenant_id)
    {

    }

    public function handle()
    {
        $this->setDrivers();

        $files = ["grid/",'large/','thumb/','tiny/',''];
        $item = $this->item;
        $tenant_id = $this->tenant_id;

        foreach($files as $vFile){
            $prefix = '';
            if ($vFile != ''){
                $prefix = str_replace('/','',$vFile).'-';
            }

            $local_file_path = base_path("../assets/tenant/uploads/media-uploader/".$tenant_id."/".$vFile.$prefix.$item->path);
            $cl_file_path = $tenant_id."/".$vFile.$prefix.$item->path;

            /* checking the file exits in locally or not, if not exits return this jobs. */
            if (empty($item->path)){
                return;
            }

            if (!file_exists($local_file_path)){
                return;
            }

            //todo:: check the file already exists in the cloud if not exits then create then copy that file to cloud
            if (!Storage::exists($cl_file_path))
            {
                $fileNeed =  new \Illuminate\Http\File($local_file_path);
                //todo: have to check for three file
                Storage::putFileAs("/".$tenant_id."/".$vFile,$fileNeed,$prefix.$item->path);
                MediaUploader::find($item->id)->update(["is_synced" => 1]);
            }
        }
    }

    private function setDrivers()
    {
        $driver = get_static_option_central('storage_driver', 'TenantMediaUploader');

        if (in_array($driver, ['wasabi', 's3', 'cloudFlareR2']))
        {
            $db_name = match ($driver)
            {
                "wasabi" => "wasabi",
                "s3" => "aws",
                "cloudFlareR2" => "cloudflare_r2"
            };

            Config::set([
                "filesystems.default" => $driver,
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
