@extends('tenant.admin.admin-master')

@section('title')
    {{ __('Domain Reseller Plugin') }}
@endsection

@section('style')
    <style>
        .offer-card {
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
            text-align: center;
        }

        .domain-suggestion{
            max-width: 350px;
        }

        .original-price {
            text-decoration: line-through;
            color: #6c757d;
        }

        .sale-price {
            color: #198754;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .badge-exact-match {
            background-color: #0d6efd;
            color: white;
            font-size: 0.9rem;
            position: absolute;
            top: 1rem;
            left: 1rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }

        .why-great {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 15px;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }

        .domain-input-wrapper {
            width: 90dvw;
        }

        .domain-button-wrapper {
            width: 20dvw;
        }

        .agreement-heading{
            background-color: #b66dff;
            color: #ffffff;
        }
        .agreement-content{
            height: 380px;
            overflow-y: scroll;
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
                            <h4 class="header-title mb-2">{{__("Agreement to the policy")}}</h4>
                            <p>{{__('To proceed further and continue, kindly read and agree to our policies')}}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="body-wrap">
                                <div class="domain-availability-wrapper">
                                    @if($agreements['status'])
                                            <div class="card">
                                                <div class="card-header">
                                                    <div class="agreement-content">
                                                        @foreach($agreements['result'] ?? [] as $item)
                                                            <div @class(['mt-5' => $loop->last])>
                                                                <h3 class="agreement-heading p-2">{{$item->agreementKey}}</h3>
                                                                <p>{!! $item->content !!}</p>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                    @else
                                        <script>
                                            location.reload();
                                        </script>
                                    @endif
                                </div>

                                <div class="mt-5 d-flex justify-content-between">
                                    <div class="form-check mx-4">
                                        <input class="form-check-input agreement-check" type="checkbox" value="" id="flexCheckDefault">
                                        <label class="form-check-label m-0" for="flexCheckDefault">
                                            {{__('Agree to our policies')}}
                                        </label>
                                    </div>

                                    <a class="btn btn-success to-checkout-btn" href="{{route('tenant.admin.domain-reseller.checkout')}}">{{__('Continue to Checkout')}}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function ($) {
            "use strict"

            $(document).on('click', '.to-checkout-btn', function (e) {
                e.preventDefault();

                let checkbox = $('.agreement-check').is(':checked');
                if (!checkbox)
                {
                    // todo: store the user IP address who agrees the terms and conditions and send it to GoDaddy while purchase
                    toastr.warning('You have to agree to the policies to continue.');
                    return false;
                }

                location.href = $('.to-checkout-btn').attr('href');
            });
        })(jQuery);
    </script>
@endsection
