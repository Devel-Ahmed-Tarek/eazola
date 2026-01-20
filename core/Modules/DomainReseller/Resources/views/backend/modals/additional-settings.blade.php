<div class="modal fade" tabindex="-1" id="additional_settings_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-capitalize">{{__("Additional Settings")}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{route('landlord.admin.domain-reseller.additional.settings.update')}}" method="POST">
                @csrf

                @php
                    $fee_title = __(get_static_option_central('domain_reseller_additional_fee_title'));
                    $fee_amount = get_static_option_central('domain_reseller_additional_charge');
                @endphp
                <div class="card-body">
                    <div class="form-group mb-2">
                        <label for="additional-fee-title">{{__('Additional Fee Title')}}</label>
                        <input type="text" name="additional_fee_title" id="additional-fee-title" class="form-control mt-2" value="{{$fee_title ?? ''}}">
                        <p>
                            <small>{{__('It will be shown at the place of additional fee label in checkout')}}</small>
                        </p>
                    </div>

                    <div class="form-group mb-2">
                        <label for="additional-charge">{{__('Domain Purchase Additional Fee (USD)')}}</label>
                        <input type="number" value="{{$fee_amount ?? 0}}" min="0" name="additional_charge" id="additional-charge" class="form-control mt-2">
                    </div>

                    <button type="submit" id="update" class="btn btn-primary mt-4 pr-4 pl-4">{{__('Update Changes')}} <x-btn.button-loader class="d-none"/></button>
                </div>
            </form>
        </div>
    </div>
</div>
