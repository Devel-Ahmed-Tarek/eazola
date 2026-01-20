@extends('tenant.admin.admin-master')

@section('title')
    {{ __('Domain Reseller Plugin') }}
@endsection

@section('style')
    <style>
        .cart-container {
            max-width: 600px;
            margin: auto;
        }

        .domain-item {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 10px;
        }

        .domain-name {
            font-weight: bold;
        }

        .price {
            color: #009900;
            font-weight: bold;
        }

        .original-price {
            text-decoration: line-through;
            color: #999;
        }

        .discount {
            color: red;
            font-weight: bold;
        }

        .protection, .email-offer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .email-offer p {
            margin-bottom: 0;
        }

        .btn-custom {
            background-color: #007bff;
            color: white;
            border-radius: 20px;
            padding: 10px 25px;
        }

        .recommended-tag {
            font-size: 0.8rem;
            color: #007bff;
        }

        .line-through {
            text-decoration: line-through;
        }

        .text-small {
            font-size: 0.8rem;
        }

        .text-highlight {
            color: #ff0000;
        }

        .text-secondary {
            color: #6c757d;
        }

        .text-success {
            color: #28a745;
        }

        .text-bold {
            font-weight: bold;
        }

        .margin-top-20 {
            margin-top: 20px;
        }

        .padding-20 {
            padding: 20px;
        }

        .bg-light {
            background-color: #f8f9fa !important;
        }

        .border-rounded {
            border-radius: 0.25rem;
        }

        .www-wrapper {
            background-color: #0b0b0b;
            padding: 25px 15px;
            border-radius: 10px;
        }

        .www {
            color: #ffffff;
            vertical-align: center;
            margin: 0;
            font-size: 20px;
            font-weight: 800;
        }

        .form-check {
            margin-left: 40px;
        }

        .form-check .form-check-label {
            margin: 0;
        }

        .order-summary {
            border: 1px solid #ccc;
            padding: 20px;
            margin: 0 auto;
        }

        .order-summary h2 {
            text-align: left;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }

        .subtotal,.total,.fee {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
        }

        .promo-code {
            text-align: center;
            margin: 20px 0;
            color: #555;
            cursor: pointer;
            font-size: 14px;
        }

        .savings {
            text-align: center;
            margin: 20px 0;
            color: green;
            font-size: 14px;
        }

        .pay-button {
            display: block;
            width: 100%;
            padding: 10px;
            border: none;
            background-color: black;
            color: white;
            text-align: center;
            cursor: pointer;
        }

        .payment-gateway-list {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .payment-gateway-list > ul,
        .payment_getway_image > ul {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin: 0;
            padding: 0;
        }
        .payment-gateway-list > ul li,
        .payment_getway_image ul li {
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ddd;
            height: 50px;
            width: 98px;
            overflow: hidden;
        }
        .payment-gateway-list > ul li img,
        .payment_getway_image ul li img {
            height: 100%;
            width: 100%;
            object-fit: cover;
        }

        .payment-gateway-wrapper ul li:is(.selected){
            animation: growingBorder 2s linear;
            border: 2px solid #0d6efd;
        }

        .payment-gateway-wrapper ul li:not(.selected){
            opacity: 0.60;
            cursor: pointer;
        }
    </style>
@endsection

@section('content')
    <div class="dashboard-recent-order">
        <div class="row">
            <div class="col-md-12">
                <x-flash-msg/>
                <x-error-msg/>

                <div class="p-4 recent-order-wrapper dashboard-table bg-white padding-30">
                    <div class="wrapper d-flex justify-content-between">
                        <div class="header-wrap">
                            <h4 class="header-title mb-2">{{__("Checkout")}}</h4>
                            <p>{{__('To proceed further and continue, kindly fill up the form')}}</p>
                        </div>
                    </div>

                    @php
                        $full_domain = $data['data']['domain'];
                        $exploded = explode('.', $full_domain);
                        $extension = last($exploded);
                    @endphp
                    <form action="{{route('tenant.admin.domain-reseller.checkout')}}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-8">
                                <div class="body-wrap">
                                        @csrf
                                        <div class="bg-light border-rounded padding-20">
                                            <div class="domain-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex gap-3">
                                                        <div class="www-wrapper">
                                                            <p class="www">WWW</p>
                                                        </div>
                                                        <div class="domain-name">
                                                            <h3 class="h5">{{$full_domain}}</h3>
                                                            <p class="text-secondary">
                                                                .{{strtoupper($extension)}} {{__('Domain Registration')}}</p>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h3 class="price text-success">{{$data['data']['currency'] === 'USD' ? "$" : $data['data']['currency']}}{{$data['data']['price']}}</h3>
                                                    </div>
                                                </div>
                                                <div class="mt-3 d-flex justify-content-between">
                                                    <div>
                                                        <p class="renew-expire-text">
                                                            <span>{{__('1 Year')}}</span> <span class="text-small">{{__('Expires on')}}</span>
                                                        </p>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="period">{{__('Select Period')}}</label>
                                                        <select name="period" id="period" class="form-control">
                                                            <option value="1">{{__('1 year validity')}}</option>
                                                            <option value="2">{{__('2 year validity')}}</option>
                                                            <option value="3">{{__('3 year validity')}}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-light border-rounded padding-20 mt-4">
                                            <h4>{{__('Contact Admin')}}</h4>
                                            <div class="domain-item">
                                                <div class="row gx-3">
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label for="">{{__('First name')}}
                                                                <x-fields.mandatory-indicator/>
                                                            </label>
                                                            <input type="text" class="form-control" name="nameFirst" value="{{old('nameFirst')}}" placeholder="{{__('Write first name')}}">
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label for="">{{__('Last name')}}
                                                                <x-fields.mandatory-indicator/>
                                                            </label>
                                                            <input type="text" class="form-control" name="nameLast" value="{{old('nameLast')}}" placeholder="{{__('Write last name')}}">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="">{{__('Middle name')}}</label>
                                                    <input type="text" class="form-control" name="nameMiddle" value="{{old('nameMiddle')}}" placeholder="{{__('Write middle name')}}">
                                                </div>

                                                <div class="row gx-3">
                                                    <div class="col-4">
                                                        <div class="form-group">
                                                            <label for="">{{__('Email')}}
                                                                <x-fields.mandatory-indicator/>
                                                            </label>
                                                            <input type="email" class="form-control" name="email" value="{{old('email')}}" placeholder="{{__('Write email address')}}">
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="form-group">
                                                            <label for="">{{__('Phone')}}
                                                                <x-fields.mandatory-indicator/>
                                                            </label>
                                                            <input type="text" class="form-control" name="phone" value="{{old('phone')}}" placeholder="{{__('Example +1.12345 67890')}}">
                                                            <p>
                                                                <small>{{__('Write phone number separate with dot (.)')}}</small>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="form-group">
                                                            <label for="">{{__('Fax')}}</label>
                                                            <input type="text" class="form-control" name="fax" value="{{old('fax')}}" placeholder="{{__('Example +1.12345 67890')}}">
                                                            <p>
                                                                <small>{{__('Write fax number separate with dot (.)')}}</small>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row gx-3">
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label for="">{{__('Job Title')}}</label>
                                                            <input type="text" class="form-control" name="jobTitle" value="{{old('jobTitle')}}" placeholder="{{__('Write jon title')}}">
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label for="">{{__('Organization')}}</label>
                                                            <input type="text" class="form-control" name="organization" value="{{old('organization')}}" placeholder="{{__('Write organization name')}}">
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
                                                            <select class="form-control country" name="country" id="country">
                                                                <option value="">{{__('Select a country')}}</option>
                                                                @foreach($data['countries'] ?? [] as $country)
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
                                                            <select class="form-control state" name="state" id="state"></select>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="city">{{__('City')}}
                                                                <x-fields.mandatory-indicator/>
                                                            </label>
                                                            <input type="text" class="form-control" name="city" id="city" value="{{old('city')}}" placeholder="{{__('Write city name')}}">
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="form-group">
                                                            <label for="postalCode">{{__('Postal Code')}}
                                                                <x-fields.mandatory-indicator/>
                                                            </label>
                                                            <input type="text" class="form-control" name="postalCode" id="postalCode" value="{{old('postalCode')}}" placeholder="{{__('Write postal code')}}">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="address1">{{__('Address One')}}
                                                            <x-fields.mandatory-indicator/>
                                                        </label>
                                                        <textarea name="address1" class="form-control" id="address1" cols="30" rows="10">{{old('address1')}}</textarea>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="address2">{{__('Address Two')}}</label>
                                                        <textarea name="address2" class="form-control" id="address2" cols="30" rows="10">{{old('address2')}}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <x-domainreseller::domain-checkout-form title="Contact Billing" key="contact_billing" :countries="$data['countries']"/>
                                        <x-domainreseller::domain-checkout-form title="Contact Registrant" key="contact_registrant" :countries="$data['countries']"/>
                                        <x-domainreseller::domain-checkout-form title="Contact Tech" key="contact_tech" :countries="$data['countries']"/>
                                </div>
                            </div>

                            <div class="col-4">
                                <div class="order-summary">
                                    <h2>Order Summary</h2>
                                    <div class="d-flex justify-content-between mt-5">
                                        <p>{{__('Subtotal')}} </p>
                                        <div class="subtotal">
                                            <span>(USD) ${{$data['data']['price']}}</span>
                                        </div>
                                    </div>

                                    @php
                                        $fee_title = __(get_static_option_central('domain_reseller_additional_fee_title') ?? 'Platform Fee');
                                        $fee_amount = get_static_option_central('domain_reseller_additional_charge') ?? 0;
                                    @endphp
                                    <div class="d-flex justify-content-between mb-5">
                                        <p class="text-capitalize">{{$fee_title}}</p>
                                        <div class="fee">
                                            <span>(USD) ${{$fee_amount}}</span>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="d-flex justify-content-between mb-5">
                                        <p class="text-capitalize">{{__('Total')}}</p>
                                        <div class="total">
                                            <span>(USD) ${{$data['data']['price'] + $fee_amount}}</span>
                                        </div>
                                    </div>

                                    <div class="payment-gateways">
{{--                                        {!! \App\Helpers\PaymentGatewayRenderHelper::renderPaymentGatewayForForm(['manual_payment']) !!}--}}
                                        <div class="payment_getway_image">
                                            {!! render_payment_gateway_for_form(exclude: ['bank_transfer', 'manual_payment_']) !!}
                                        </div>
                                    </div>

                                    <button type="submit" class="pay-button mt-4">{{__("I'm Ready to Pay")}} <x-btn.button-loader class="d-none"/></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function ($) {
            "use strict"

            $(document).ready(function () {
                let counterText = $('.alert.alert-danger p');

                if (counterText.text().includes('1 minutes')) {
                    let counter = 60; // 60 seconds countdown
                    let interval = setInterval(function() {
                        counter--;
                        if (counter <= 0) {
                            clearInterval(interval);
                            counterText.parent().slideUp();
                        } else {
                            counterText.text("Can not proceed duplicate payment, wait for next " + counter + " seconds");
                        }
                    }, 1000);
                }
            });

            $(document).on('click', '.payment-gateway-wrapper ul li', function () {
                let el = $(this);
                let gateway_name = el.attr('data-gateway');

                $('.payment-gateway-wrapper ul li').removeClass('selected');
                el.addClass('selected');

                $('input[name=selected_payment_gateway]').val(gateway_name);
            });

            $(document).on('click', '.expand-collapse-btn', function (e)
            {
                e.preventDefault();

                let el = $(this);
                el.closest('.contact-parent-wrapper').find('.contact-form-wrapper').toggleClass('d-none');
            })

            $('.contact-parent-wrapper').find('input, textarea, select').attr('disabled', true);
            $('.same-contact-admin').attr('disabled', false);

            $(document).on('click', '.same-contact-admin', function ()
            {
                let el = $(this);
                let parent = el.closest('.contact-parent-wrapper');

                if (el.is(':checked'))
                {
                    parent.find('input, textarea, select').attr('disabled', true)
                    el.attr('disabled', false)
                    parent.find('.contact-form-wrapper').addClass('d-none');
                    el.siblings('.was_unchecked').val("");
                } else {
                    parent.find('input, textarea, select').attr('disabled', false)
                    parent.find('.contact-form-wrapper').removeClass('d-none');
                    el.siblings('.was_unchecked').val("yes");
                }
            });

            $(document).on('click', '.pay-button', function () {
                $(this).find('span').removeClass('d-none');
            });

            $(document).on('change', 'select.country', function (e)
            {
                let el = $(this);
                let selected_countryKey = e.target.value;
                let select_option_wrapper = el.closest('.row').find('.state');

                $.ajax({
                    url: `{{route('tenant.admin.domain-reseller.states')}}?countryKey=${selected_countryKey}`,
                    type: 'GET',
                    beforeSend: function ()
                    {
                        select_option_wrapper.html(`<option value=''>Loading...</option>`);
                        select_option_wrapper.attr('placeholder', 'Loading...');
                    },
                    success: function (response)
                    {
                        if (response.status && response.states.states.length > 0)
                        {
                            let state_options = `<option value=''>Select a state</option>`;
                            let states = response.states.states;
                            states.forEach((value) => {
                                state_options += `<option value='${value.stateKey}'>${value.label}</option>`;
                            })

                            if (select_option_wrapper.prop('tagName') === 'INPUT')
                            {
                                select_option_wrapper.replaceWith(`<select class="form-control state" name="state" id="state">${state_options}</select>`);
                                select_option_wrapper = $(this).closest('.row').find('.state'); // Update reference to the new select element
                            }
                            else
                            {
                                select_option_wrapper.html(state_options);
                            }
                        } else {
                            select_option_wrapper.replaceWith(`<input class="form-control state" name="state" id="state" placeholder="Write state name">`);
                            select_option_wrapper = $(this).closest('.row').find('.state'); // Update reference if it's replaced with an input
                        }
                    },
                    error: function ()
                    {
                        select_option_wrapper.html(`<option value=''>Something went wrong</option>`);
                    }
                })
            });


            // initialization
            const domainData = {
                domain: `{{$data['data']['domain']}}`,
                price: {{round($data['data']['price'], 3)}},
                fee: {{round($fee_amount, 3)}},
                currency: `{{$data['data']['currency']}}`,
                renew: false,
                period: 1
            };

            $(document).on('change', 'select[name=period]', function () {
                let el = $(this);
                domainData.period = el.val();
                finalRender();
            });

            function renewExpire() {
                let renew_expire_el = $('.renew-expire-text');
                let renew_el = $('input[name=auto_renew]');

                domainData.renew = true;
                if (renew_el.is(':checked')) {
                    renew_expire_el.html(`
                    <span>${domainData.period} Year</span> <span class="text-small">{{__('Renews on')}} ${getDate()}</span>
                `);
                } else {
                    domainData.renew = false;
                    renew_expire_el.html(`
                    <span>${domainData.period} Year</span> <span class="text-small">{{__('Expires on')}} ${getDate()}</span>
                `);
                }
            }

            function getDate() {
                const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                const date = new Date();

                return `${months[date.getMonth()]} ${date.getFullYear() + Number(domainData.period)}`;
            }

            function getNewPrice() {
                let currency_symbol = domainData.currency === 'USD' ? '$' : domainData.currency;

                let price = domainData.price * domainData.period;
                let total = (domainData.price * domainData.period) + Number(domainData.fee);

                $('.price').html(currency_symbol + price);
                $('.subtotal span').html(`(${domainData.currency}) ${currency_symbol+price}`);
                $('.total span').html(`(${domainData.currency}) ${currency_symbol + total.toPrecision(3)}`);
            }

            function finalRender() {
                renewExpire();
                getNewPrice();
            }

            finalRender();

            const oldValues = {
                contact_billing_was_unchecked: `{{old('contact_billing_was_unchecked')}}`,
                contact_registrant_was_unchecked: `{{old('contact_registrant_was_unchecked')}}`,
                contact_tech_was_unchecked: `{{old('contact_tech_was_unchecked')}}`
            };

            for (const key in oldValues) {
                if (oldValues.hasOwnProperty(key)) {
                    const value = oldValues[key];

                    if (value !== '')
                    {
                        let el = $(`input[name=${key}]`);
                        el.siblings('.same-contact-admin').attr('checked', false);
                        el.closest('.contact-parent-wrapper').find('.contact-form-wrapper').removeClass('d-none');
                        el.closest('.contact-parent-wrapper').find('.contact-form-wrapper').find('input,select,textarea').attr('disabled', false);
                    }
                }
            }
        })(jQuery);
    </script>
@endsection
