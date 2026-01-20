@extends('tenant.admin.admin-master')

@section('title')
    {{ __('Renew Domain - Domain Reseller Plugin') }}
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
                            <h4 class="header-title mb-2">{{__("Renew Domain")}}</h4>
                            <p>{{__('To proceed further and continue, kindly fill up the form')}}</p>
                        </div>
                    </div>

                    @php
                        $full_domain = $order_details->domain;
                        $extension = last(explode('.', $full_domain));
                    @endphp

                    <form action="{{route('tenant.admin.domain-reseller.renew', wrap_random_number($order_details->id))}}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-12">
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
                                                            <p>{{__('Previous Expire Date:')}} {{$order_details->expire_at?->format('d-M-Y')}}</p>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h3 class="price text-success">${{$order_details->domain_price}}</h3>
                                                    </div>
                                                </div>
                                                <div class="mt-3 d-flex justify-content-between">
                                                    <div>
                                                        <p class="renew-expire-text">
                                                            <span>{{__('1 Year')}}</span> <span class="text-small">{{__('Expires on').' '.now()->addYear()->format('F Y')}}</span>
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
                                </div>
                            </div>

                            <div class="col-12 mt-2">
                                <div class="order-summary">
                                    <h2>{{__('Order Summary')}}</h2>
                                    <div class="d-flex justify-content-between mt-5">
                                        <p>{{__('Subtotal')}} </p>
                                        <div class="subtotal">
                                            <span>(USD) ${{$order_details->domain_price}}</span>
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
                                            <span>(USD) ${{$order_details->domain_price + $fee_amount}}</span>
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

            $(document).on('click', '.payment-gateway-wrapper ul li', function () {
                let el = $(this);
                let gateway_name = el.attr('data-gateway');

                $('.payment-gateway-wrapper ul li').removeClass('selected');
                el.addClass('selected');

                $('input[name=selected_payment_gateway]').val(gateway_name);
            });

            $(document).on('click', '.pay-button', function () {
                $(this).find('span').removeClass('d-none');
            });

            // initialization
            const domainData = {
                domain: `{{$order_details->domain}}`,
                price: `{{$order_details->domain_price}}`,
                fee: `{{$fee_amount}}`,
                currency: `USD`,
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

                return `${months[date.getMonth()]} ${date.getFullYear() + parseInt(domainData.period)}`;
            }

            function getNewPrice() {
                let currency_symbol = domainData.currency === 'USD' ? '$' : domainData.currency;

                let price = domainData.price * domainData.period;
                let total = (domainData.price * domainData.period) + parseInt(domainData.fee);

                $('.price').html(currency_symbol + price);
                $('.subtotal span').html(`(${domainData.currency}) ${currency_symbol+price}`);
                $('.total span').html(`(${domainData.currency}) ${currency_symbol+total}`);
            }

            function finalRender() {
                renewExpire();
                getNewPrice();
            }
        })(jQuery);
    </script>
@endsection
