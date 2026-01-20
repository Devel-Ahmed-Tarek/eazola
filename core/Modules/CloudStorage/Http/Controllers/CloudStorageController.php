<?php

namespace Modules\CloudStorage\Http\Controllers;

use App\Jobs\SyncLocalFileWithCloud;
use App\Jobs\SyncTenantLocalFileWithCloud;
use App\Models\MediaUploader;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Stancl\Tenancy\Facades\Tenancy;

class CloudStorageController extends Controller
{
    public function index()
    {
        return view('cloudstorage::admin.index');
    }

    public function update_storage(Request $request)
    {
        if ( $request->_action === 'sync_file')
        {
            MediaUploader::where("is_synced", 0)->chunk(50,function ($items){
                foreach ($items as $item)
                {
                    SyncLocalFileWithCloud::dispatch($item);
                }
            });

            Tenant::chunk(5,function ($items) {
                foreach ($items as $item) {
                    Tenancy::initialize($item->id);
                    //todo:: get all file list for tenant, and put it into the jobs for upload into clouldflare
                    //todo switch database connect to current tenant

                    //todo:: fetch all media image and pass to the tenant file sync
                    MediaUploader::where("is_synced", 0)->chunk(50, function ($mediaFiles) use ($item) {
                        foreach ($mediaFiles as $mfile) {
                            SyncTenantLocalFileWithCloud::dispatch($mfile, $item->id)->onConnection('tenant_file_sync')->delay(now()->addSeconds(2));
                        }
                    });

                    //todo reset database connection back to the central connection
                }
            });

            return back()->with(['msg' => __('File Sync Started In The Background'), 'type' => 'success']);
        }

        $rules = [
            'storage_driver' => 'required|string|max:191',

            'wasabi_access_key_id' => 'required_if:storage_driver,==,wasabi',
            'wasabi_secret_access_key' => 'required_if:storage_driver,==,wasabi',
            'wasabi_default_region' => 'required_if:storage_driver,==,wasabi',
            'wasabi_bucket' => 'required_if:storage_driver,==,wasabi',
            'wasabi_endpoint' => 'required_if:storage_driver,==,wasabi',

            'cloudflare_r2_access_key_id' => 'required_if:storage_driver,==,cloudFlareR2',
            'cloudflare_r2_secret_access_key' => 'required_if:storage_driver,==,cloudFlareR2',
            'cloudflare_r2_bucket' => 'required_if:storage_driver,==,cloudFlareR2',
            'cloudflare_r2_url' => 'required_if:storage_driver,==,cloudFlareR2',
            'cloudflare_r2_endpoint' => 'required_if:storage_driver,==,cloudFlareR2',
            'cloudflare_r2_use_path_style_endpoint' => 'required_if:storage_driver,==,cloudFlareR2',

            'aws_access_key_id' => 'required_if:storage_driver,==,s3',
            'aws_secret_access_key' => 'required_if:storage_driver,==,s3',
            'aws_default_region' => 'required_if:storage_driver,==,s3',
            'aws_bucket' => 'required_if:storage_driver,==,s3',
            'aws_url' => 'required_if:storage_driver,==,s3',
            'aws_endpoint' => 'required_if:storage_driver,==,s3',
            'aws_use_path_style_endpoint' => 'required_if:storage_driver,==,s3',
        ];


        $request->validate($rules);

        foreach ($rules as $index => $value)
        {
            update_static_option_central($index, $request->$index);
        }

        return back()->with(['msg' => __('Storage Settings Updated'), 'type' => 'success']);
    }
}
