<div class="modal fade" tabindex="-1" id="godaddy_configuration_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-capitalize">{{__("GoDaddy")}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{route('landlord.admin.domain-reseller.configuration.update')}}" method="POST">
                @csrf

                <input type="hidden" name="shipping_gateway_name" value="goDaddy">
                <div class="card-body">
                    <!--otp env settings -->
                    <h5 class="mb-4">{{ __('Configure GoDaddy Options') }}</h5>

                    <div class="mb-2">
                        <x-fields.switcher label="Enable live mode" name="godaddy_environment"
                                           info="{{__('Integrate first with the test environment to verify that you are calling the API properly before going live with calls to the Production environment.')}}"
                                           info-class="alert alert-info text-dark"
                                           set-value="on" value="{{get_static_option_central('godaddy_environment')}}"/>
                    </div>

                    <button type="submit" id="update" class="btn btn-primary mt-4 pr-4 pl-4">{{__('Update Changes')}} <x-btn.button-loader class="d-none"/></button>
                </div>
            </form>
        </div>
    </div>
</div>
