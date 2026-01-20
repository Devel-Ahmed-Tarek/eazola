@extends('tenant.admin.admin-master')

@section('title')
    {{ __('Domain Reseller Plugin - Order Canceled') }}
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

                <div class="p-4 py-5 recent-order-wrapper dashboard-table bg-white padding-30">
                    <div class="wrapper">
                        <div class="header-wrap text-center">
                            <h2 class="header-title text-uppercase text-danger mb-3">{{__("The order is unsuccessful")}}</h2>
                            <h3>{{$order_details->domain .' - '. $order_details->period . ' Year'}}</h3>

                            <div class="card">
                                <div class="card-body pb-2 mt-3" style="background: #f8f8f8;">
                                    @php
                                        $statusText = '\Modules\DomainReseller\Http\Enums\StatusEnum::getText';
                                        $colors = [0 => 'text-danger', 1 => 'text-success']
                                    @endphp
                                    <h4 class="text-capitalize">
                                        <strong>{{__('Payment Status:')}}</strong>
                                        <span class="m-2 {{$colors[$order_details->payment_status]}}">{{$statusText($order_details->payment_status)}}</span>
                                    </h4>

                                    <h4 class="text-capitalize">
                                        <strong>{{__('Domain Status:')}}</strong>
                                        <span class="m-2 {{$colors[$order_details->status]}}">{{$statusText($order_details->status)}}</span>
                                    </h4>
                                    <p class="mt-3">{{__('The reason could be, the service may not be available at your geo location. Contact admin for further actions')}}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
