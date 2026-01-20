@extends(route_prefix().'admin.admin-master')

@section('title')
    {{tenant() ?  __('Pending Domain List - Domain Reseller Plugin') : __('Failed Domain List - Domain Reseller Plugin')}}
@endsection

@section('style')
    <style>
        .domain-reseller-loader-wrapper {
            width: 100%;
            height: 100%;
            background: #ffffffe8;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 9999
        }

        .loader-02{
            left: 50dvw;
            top: 40dvh;
            position: absolute;
        }
        .loader-text{
            left: 0;
            top: 15dvh;
            position: absolute;
        }
    </style>
@endsection

@section('content')
    <div class="domain-reseller-loader-wrapper" style="display: none">
        <x-loaders.loader-02/>
    </div>

    <div class="dashboard-recent-order">
        <div class="row">
            <div class="col-md-12">
                <x-flash-msg/>
                <x-error-msg/>

                <div class="p-4 recent-order-wrapper dashboard-table bg-white padding-30">
                    <div class="wrapper d-flex justify-content-between">
                        <div class="header-wrap">
                            <h4 class="header-title mb-2">{{tenant() ? __("Pending Domains") : __('Failed Domains')}}</h4>
                            <p>{{__('All your purchased domain are listed below.')}}</p>
                        </div>

                        <div class="d-flex justify-content-between gap-2">
                            <div class="btn-wrapper">
                                <a href="{{route(route_prefix().'admin.domain-reseller.list.domain')}}"
                                   class="btn btn-outline-info btn-sm d-flex gap-2">
                                    <i class="mdi mdi-arrow-left menu-icon"></i>
                                    <span>{{__('Back')}}</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="body-wrap my-4">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th scope="col">{{__('# ID')}}</th>
                                <th scope="col">{{__('Email')}}</th>
                                <th scope="col">{{__('Domain')}}</th>
                                <th scope="col">{{__('Period')}}</th>
                                <th scope="col">{{__('Price')}}</th>
                                <th scope="col">{{__('Payment Status')}}</th>
                                <th scope="col">{{__('Domain Status')}}</th>
                                <th scope="col">{{__('Renew')}}</th>
                                <th scope="col">{{__('Created At')}}</th>
                                @if(!tenant())
                                    <th scope="col">{{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($domain_list ?? [] as $index => $item)
                                @php
                                    $getText = '\Modules\DomainReseller\Http\Enums\StatusEnum::getText';
                                @endphp

                                <tr>
                                    <th scope="row">{{$loop->iteration}}</th>
                                    <td>{{$item->email}}</td>
                                    <td>{{$item->domain}}</td>
                                    <td>{{$item->period}} {{__('Year')}}</td>
                                    <td>{{amount_with_currency_symbol($item->domain_price + $item->extra_fee)}}</td>
                                    <td>
                                        <p class="text-capitalize {{$item->payment_status ? 'text-success' : 'text-danger'}}">{{$getText($item->payment_status, true)}}</p>
                                    </td>
                                    <td>
                                        <p class="text-capitalize text-danger">{{$getText($item->status)}}</p>
                                    </td>
                                    <td>{{$item->purchase_count > 1 ? __('Renew') : __('New Purchase')}}</td>
                                    <td>{{$item->updated_at->format('d-M-Y')}}</td>
                                    @if(!tenant())
                                        <td>
                                            <a href="{{route('landlord.admin.domain-reseller.list.domain.failed.complete', wrap_random_number($item->id))}}" class="re-purchase-btn btn btn-info btn-sm">{{__('Complete Action')}}</a>
                                        </td>
                                    @endif
                                </tr>
                                @empty
                                    <tr>
                                        <td class="text-center" colspan="10">{{__('No data available')}}</td>
                                    </tr>
                            @endforelse
                            </tbody>
                        </table>
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

            $(document).ready(function () {
                $(document).on('click', '.re-purchase-btn', function (e) {
                    e.preventDefault();

                    Swal.fire({
                        title: `{{__('Are you sure?')}}`,
                        text: `{{__("You won't be able to revert those!")}}`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33333',
                        confirmButtonText: `{{__('Continue')}}`
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.href = $(this).attr('href');
                        }
                    })
                });
            })
        })(jQuery);
    </script>
@endsection
