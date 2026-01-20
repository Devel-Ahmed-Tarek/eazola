<?php

namespace App\Jobs;

use App\Http\Middleware\Tenant\TenantConfigMiddleware;
use App\Models\MediaUploader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SyncLocalFileWithCloud implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public $file)
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->setDrivers();
        $files = ["grid/",'large/','thumb/','tiny/',''];
        $item = $this->file;

        foreach ($files as $vFile) {
            $prefix = '';
            if ($vFile != ''){
                $prefix = str_replace('/','',$vFile).'-';
            }

            //todo, run query from the database get all media file then run loop and send file to the jobs done it through queue, update database that this file is already synced
            $local_file_path = base_path("../assets/landlord/uploads/media-uploader/".$vFile.$prefix.$item?->path);

            $cl_file_path = $vFile.$prefix.$item?->path;

            // /* checking the file exits in locally or not, if not exits return this jobs. */
            if (empty($item->path)){
                return;
            }
            if (!file_exists($local_file_path)){
                return;
            }

            // //todo:: check the file already exists in the cloud if not exits then create then copy that file to cloud
            if (!Storage::exists($cl_file_path))
            {
                $fileNeed =  new File($local_file_path);
                //todo: have to check for three file
                Storage::putFileAs("/".$vFile,$fileNeed,$prefix.$item->path, 'public');
                MediaUploader::find($item->id)->update(["is_synced" => 1,'load_from' => 1]);
            }

            /* change the database status to is_synced because the file is already exits on the cloud */
            MediaUploader::find($item->id)->update(["is_synced" => 1,'load_from' => 1]);
        }
    }

    private function setDrivers()
    {
        $driver = get_static_option_central('storage_driver', empty(tenant()) ? 'LandlordMediaUploader' : 'TenantMediaUploader');

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
