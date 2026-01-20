@extends(route_prefix().'admin.admin-master')
@section('title')
    {{__('Storage Settings')}}
@endsection
@section('style')
    <x-media-upload.css/>

    <style>
        .credentials{
            display: none;
        }
        .info{
            cursor: pointer;
        }
        .info:hover{
            color: #009eff;
        }
        .cloud-alert b{
            cursor: pointer;
        }
        .sync-btn {
            transition: 0.1s;
        }
    </style>
@endsection
@section('content')
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <x-admin.header-wrapper class="mb-2">
                    <x-slot name="left">
                        <h4 class="card-title mb-5">{{__('Storage Settings')}} <span class="info mdi mdi-alpha-i-circle-outline"></span></h4>
                    </x-slot>

                    <x-slot name="right" class="d-flex">
                        <form action="{{route('landlord.admin.cloud.storage.settings')}}" method="post">
                            @csrf
                            <input type="hidden" name="_action" value="sync_file">
                            <button class="sync-btn btn btn-info btn-sm"
                                    type="submit">{{__('Sync Local File To Cloud')}}</button>
                        </form>
                    </x-slot>
                </x-admin.header-wrapper>

                <div class="cloud-alert alert alert-info mb-5 d-none">
                    <p>{!! __('To effectively utilize the cloud storage service, please carefully choose your preferred cloud provider and ensure the accurate entry of your respective credentials. Once the connection to the chosen cloud storage is established successfully, your media files will be seamlessly uploaded to this cloud storage whenever you utilize our integrated media uploader. Should you desire to synchronize all media files associated with both landlords and tenants from your server to the designated cloud storage, simply click on the <b>Sync Local Files to Cloud</b> button. This process will initiate the background upload of all media files. Please be aware that the duration of this operation may vary, potentially taking a considerable amount of time based on factors such as the quantity of files, file sizes, and server performance.') !!}</p>
                    <p class="text-danger bg-white mt-2 p-2">{{__('Removing this plugin while it is in use may lead to application instability or potential crashes. For further assistance and guidance, please do not hesitate to reach out to our support team')}}</p>
                </div>

                <x-error-msg/>
                <x-flash-msg/>
                <form class="forms-sample" method="post"
                      action="{{route('landlord.admin.cloud.storage.settings')}}">
                    @csrf
                    <x-fields.select name="storage_driver" id="storage-driver" title="{{__('Disks Driver')}}"
                                     info="{{__('By default it is local, if you have disk driver you can set here, unless leave this as (Local)')}}">
                        <option
                            value="LandlordMediaUploader" {{ get_static_option_central('storage_driver') == 'LandlordMediaUploader' ? 'selected' : '' }}>{{__('Local')}}</option>
                        <option
                            value="cloudFlareR2" {{ get_static_option_central('storage_driver') == 'cloudFlareR2' ? 'selected' : '' }}>{{__('cloud Flare R2')}}</option>
                        <option
                            value="wasabi" {{ get_static_option_central('storage_driver') == 'wasabi' ? 'selected' : '' }}>{{__('Wasabi s3')}}</option>
                        <option
                            value="s3" {{ get_static_option_central('storage_driver') == 's3' ? 'selected' : '' }}>{{__('Aws s3')}}</option>
                    </x-fields.select>


                    <div class="credentials_wrapper mt-5">
                        <div class="credentials wasabi" @style(['display:block' => get_static_option_central('storage_driver') == 'wasabi'])>
                            <div class="form-group">
                                <label for="">{{__('Wasabi Access Key ID')}}</label>
                                <input class="form-control" type="text" name="wasabi_access_key_id" value="{{get_static_option_central('wasabi_access_key_id')}}">
                            </div>
                            <div class="form-group">
                                <label for="">{{__('Wasabi Secret Access Key')}}</label>
                                <input class="form-control" type="text" name="wasabi_secret_access_key" value="{{get_static_option_central('wasabi_secret_access_key')}}">
                            </div>
                            <div class="form-group">
                                <label for="">{{__('Wasabi Default Region')}}</label>
                                <input class="form-control" type="text" name="wasabi_default_region" value="{{get_static_option_central('wasabi_default_region')}}">
                            </div>
                            <div class="form-group">
                                <label for="">{{__('Wasabi Bucket')}}</label>
                                <input class="form-control" type="text" name="wasabi_bucket" value="{{get_static_option_central('wasabi_bucket')}}">
                            </div>
                            <div class="form-group">
                                <label for="">{{__('Wasabi ENDPOINT')}}</label>
                                <input class="form-control" type="text" name="wasabi_endpoint" value="{{get_static_option_central('wasabi_endpoint')}}">
                            </div>
                        </div>

                        <div class="credentials cloudFlareR2" @style(['display:block' => get_static_option_central('storage_driver') == 'cloudFlareR2'])>
                            <div class="form-group">
                                <label for="">{{__('Cloudflare R2 Access Key ID')}}</label>
                                <input class="form-control" type="text" name="cloudflare_r2_access_key_id" value="{{get_static_option_central('cloudflare_r2_access_key_id')}}">
                            </div>

                            <div class="form-group">
                                <label for="">{{__('Cloudflare R2 Secret Access Key')}}</label>
                                <input class="form-control" type="text" name="cloudflare_r2_secret_access_key" value="{{get_static_option_central('cloudflare_r2_secret_access_key')}}">
                            </div>

                            <div class="form-group">
                                <label for="">{{__('Cloudflare R2 Bucket')}}</label>
                                <input class="form-control" type="text" name="cloudflare_r2_bucket" value="{{get_static_option_central('cloudflare_r2_bucket')}}">
                            </div>

                            <div class="form-group">
                                <label for="">{{__('Cloudflare R2 URL')}}</label>
                                <input class="form-control" type="text" name="cloudflare_r2_url" value="{{get_static_option_central('cloudflare_r2_url')}}">
                            </div>

                            <div class="form-group">
                                <label for="">{{__('Cloudflare R2 Endpoint')}}</label>
                                <input class="form-control" type="text" name="cloudflare_r2_endpoint" value="{{get_static_option_central('cloudflare_r2_endpoint')}}">
                            </div>

                            <div class="form-group">
                                <label for="">{{__('Cloudflare R2 Use Path Style Endpoint')}}</label>
                                <select class="form-control" name="cloudflare_r2_use_path_style_endpoint">
                                    <option value="1" {{get_static_option_central('cloudflare_r2_use_path_style_endpoint') == 1 ? 'selected' : ''}}>{{__('Yes')}}</option>
                                    <option value="0" {{get_static_option_central('cloudflare_r2_use_path_style_endpoint') == 0 ? 'selected' : ''}}>{{__('No')}}</option>
                                </select>
                            </div>
                        </div>

                        <div class="credentials s3" @style(['display:block' => get_static_option_central('storage_driver') == 's3'])>
                            <div class="form-group">
                                <label for="">{{__('AWS Access Key ID')}}</label>
                                <input class="form-control" type="text" name="aws_access_key_id" value="{{get_static_option_central('aws_access_key_id')}}">
                            </div>
                            <div class="form-group">
                                <label for="">{{__('AWS Secret Access Key')}}</label>
                                <input class="form-control" type="text" name="aws_secret_access_key" value="{{get_static_option_central('aws_secret_access_key')}}">
                            </div>
                            <div class="form-group">
                                <label for="">{{__('AWS Default Region')}}</label>
                                <input class="form-control" type="text" name="aws_default_region" value="{{get_static_option_central('aws_default_region')}}">
                            </div>
                            <div class="form-group">
                                <label for="">{{__('AWS Bucket')}}</label>
                                <input class="form-control" type="text" name="aws_bucket" value="{{get_static_option_central('aws_bucket')}}">
                            </div>
                            <div class="form-group">
                                <label for="">{{__('AWS URL')}}</label>
                                <input class="form-control" type="text" name="aws_url" value="{{get_static_option_central('aws_url')}}">
                            </div>
                            <div class="form-group">
                                <label for="">{{__('AWS Endpoint')}}</label>
                                <input class="form-control" type="text" name="aws_endpoint" value="{{get_static_option_central('aws_endpoint')}}">
                            </div>
                            <div class="form-group">
                                <label for="">{{__('AWS Use Path Style Endpoint')}}</label>
                                <select class="form-control" name="aws_use_path_style_endpoint">
                                    <option value="1" {{get_static_option_central('aws_use_path_style_endpoint') == 1 ? 'selected' : ''}}>{{__('Yes')}}</option>
                                    <option value="0" {{get_static_option_central('aws_use_path_style_endpoint') == 0 ? 'selected' : ''}}>{{__('No')}}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gradient-primary me-2">{{__('Save Changes')}}</button>
                </form>
            </div>
        </div>
    </div>
    <x-media-upload.markup/>
@endsection
@section('scripts')
    <x-media-upload.js/>
    <script>
        $(document).ready(function () {
            $(document).on('change', '#storage-driver', function () {

                let driver = $(this).val();

                Swal.fire({
                    title: "{{ __('Do you want to change this cloud driver?') }}",
                    text: '{{__("If you change your storage driver, you will need to download all the media files before to re-sync on newly selected driver")}}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{__('Change')}}',
                    confirmButtonColor: '#009eff',
                    cancelButtonText: "{{__('Discard')}}",
                    cancelButtonColor: '#ff2600'
                }).then((result) => {
                    if (result.isConfirmed) {
                        changeState(driver);
                    } else {
                        window.location.reload();
                    }
                });
            });

            function changeState(driver)
            {
                $('.credentials').hide();
                $(`.${driver}`).fadeIn();
            }

            $(document).on('click','.cloud-alert b',function () {
                let sync_btn = $('.sync-btn');

                sync_btn.css('transform', 'scale(1.2)');
                setTimeout(() => {
                    sync_btn.css('transform', 'scale(1)');
                }, 100)
            });

            $(document).on('click','.info', function () {
                $('.cloud-alert').toggleClass('d-none');
            });
        });
    </script>
@endsection
