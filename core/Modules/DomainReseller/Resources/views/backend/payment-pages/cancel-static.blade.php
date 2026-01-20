@extends('tenant.admin.admin-master')

@section('title')
    {{ __('Domain Reseller Plugin - Order Canceled') }}
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
                            <p class="header-title text-capitalize mb-3">{{__("It may happened due to internal or network issue")}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
