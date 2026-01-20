@props([
    'title',
    'key',
    'countries'
])

<div class="contact-billing-wrapper contact-parent-wrapper bg-light border-rounded padding-20 mt-4">
    <div class="d-flex justify-content-between">
        <div>
            <h4>{{__($title)}}</h4>
            <div class="form-check form-switch">
                <input type="hidden" class="was_unchecked" name="{{$key}}_was_unchecked">
                <input class="form-check-input same-contact-admin" type="checkbox" id="{{$key}}-same-contact-admin"
                       name="{{$key}}_same_contact_admin" value="selected" checked>
                <label class="form-check-label"
                       for="{{$key}}-same-contact-admin">{{__('Same as contact admin')}}</label>
            </div>
        </div>
        <a class="expand-collapse-btn" href="">
            <span class="mdi mdi-arrow-expand"></span>
        </a>
    </div>

    <div class="contact-form-wrapper domain-item mt-3 d-none">
        <div class="row gx-3">
            <div class="col-6">
                <div class="form-group">
                    <label for="">{{__('First name')}}
                        <x-fields.mandatory-indicator/>
                    </label>
                    <input type="text" class="form-control" name="{{$key}}_nameFirst" value="{{old("{$key}_nameFirst")}}">
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label for="">{{__('Last name')}}
                        <x-fields.mandatory-indicator/>
                    </label>
                    <input type="text" class="form-control" name="{{$key}}_nameLast" value="{{old("{$key}_nameLast")}}">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="">{{__('Middle name')}}</label>
            <input type="text" class="form-control" name="{{$key}}_nameMiddle" value="{{old("{$key}_nameMiddle")}}">
        </div>

        <div class="row gx-3">
            <div class="col-4">
                <div class="form-group">
                    <label for="">{{__('Email')}}
                        <x-fields.mandatory-indicator/>
                    </label>
                    <input type="email" class="form-control" name="{{$key}}_email" value="{{old("{$key}_email")}}">
                </div>
            </div>
            <div class="col-4">
                <div class="form-group">
                    <label for="">{{__('Phone')}}
                        <x-fields.mandatory-indicator/>
                    </label>
                    <input type="text" class="form-control" name="{{$key}}_phone" value="{{old("{$key}_phone")}}">
                </div>
            </div>
            <div class="col-4">
                <div class="form-group">
                    <label for="">{{__('Fax')}}</label>
                    <input type="text" class="form-control" name="{{$key}}_fax" value="{{old("{$key}_fax")}}">
                </div>
            </div>
        </div>

        <div class="row gx-3">
            <div class="col-6">
                <div class="form-group">
                    <label for="">{{__('Job Title')}}</label>
                    <input type="text" class="form-control" name="{{$key}}_jobTitle" value="{{old("{$key}_jobTitle")}}">
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label for="">{{__('Organization')}}</label>
                    <input type="text" class="form-control" name="{{$key}}_organization" value="{{old("{$key}_organization")}}">
                </div>
            </div>
        </div>

        <br>
        <h4>{{__('Address')}}</h4>
        <hr>

        <div class="row gx-3">
            <div class="col-3">
                <div class="form-group">
                    <label for="country">{{__('Country')}}
                        <x-fields.mandatory-indicator/>
                    </label>
                    <select class="form-control country" name="{{$key}}_country" id="country">
                        <option value="">{{__('Select a country')}}</option>
                        @foreach($countries ?? [] as $country)
                            <option
                                value="{{$country->countryKey}}">{{$country->label}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <label for="state">{{__('State')}}
                        <x-fields.mandatory-indicator/>
                    </label>
                    <select class="form-control state" name="{{$key}}_state" id="state"></select>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <label for="city">{{__('City')}}
                        <x-fields.mandatory-indicator/>
                    </label>
                    <input type="text" class="form-control" name="{{$key}}_city" id="city" value="{{old("{$key}_city")}}">
                </div>
            </div>
            <div class="col-3">
                <div class="form-group">
                    <label for="postalCode">{{__('Postal Code')}}
                        <x-fields.mandatory-indicator/>
                    </label>
                    <input type="text" class="form-control" name="{{$key}}_postalCode" id="postalCode" value="{{old("{$key}_postalCode")}}">
                </div>
            </div>

            <div class="form-group">
                <label for="address1">{{__('Address One')}}
                    <x-fields.mandatory-indicator/>
                </label>
                <textarea name="{{$key}}_address1" class="form-control" id="address1" cols="30" rows="10">{{old("{$key}_address1")}}</textarea>
            </div>

            <div class="form-group">
                <label for="address2">{{__('Address Two')}}</label>
                <textarea name="{{$key}}_address2" class="form-control" id="address2" cols="30" rows="10">{{old("{$key}_address2")}}</textarea>
            </div>
        </div>
    </div>
</div>
