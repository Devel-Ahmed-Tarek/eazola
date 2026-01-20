<div class="modal fade" tabindex="-1" id="godaddy_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-capitalize">{{__("GoDaddy")}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{route('landlord.admin.domain-reseller.settings.update')}}" method="POST">
                @csrf

                <div class="card-body">
                    <!--otp env settings -->
                    <h5 class="mb-4">{{ __('Configure GoDaddy Credentials') }}</h5>
                    <div class="form-group mt-3">
                        <label for=""><strong>{{__('GoDaddy API Key')}} <span class="text-danger">*</span> </strong></label>
                        <input type="text"  class="form-control" name="godaddy_api_key" value=""
                               placeholder="{{ __('GoDaddy API Key')}}">
                    </div>

                    <div class="form-group mt-3">
                        <label for=""><strong>{{__('GoDaddy API Secret')}} <span class="text-danger">*</span> </strong></label>
                        <input type="text"  class="form-control" name="godaddy_api_secret" value=""
                               placeholder="{{ __('GoDaddy API Secret')}}">
                    </div>

                    <div class="form-group mt-3">
                        <label for=""><strong>{{__('GoDaddy API App Name')}} <small><sup>(Optional)</sup></small></strong></label>
                        <input type="text"  class="form-control" name="godaddy_api_app_name" value=""
                               placeholder="{{ __('GoDaddy API App Name')}}">
                    </div>

                    <button type="submit" id="update" class="btn btn-primary mt-4 pr-4 pl-4">{{__('Update Changes')}} <x-btn.button-loader class="d-none"/></button>
                </div>
            </form>
        </div>
    </div>
</div>
