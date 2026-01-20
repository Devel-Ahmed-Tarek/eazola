@extends(route_prefix().'admin.admin-master')

@section('title')
    {{ __('Domain List - Domain Reseller Plugin') }}
@endsection

@section('style')
    <link rel="stylesheet" href="{{global_asset('assets/common/css/domain-reseller-loader.css')}}">
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

        .loader-02 {
            left: 50dvw;
            top: 40dvh;
            position: absolute;
        }

        .loader-text {
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
                            <h4 class="header-title mb-2">{{__("Purchased Domain")}}</h4>
                            <p>{{__('All your purchased domain are listed below.')}}</p>
                        </div>

                        <div class="d-flex justify-content-between gap-2">
                            <div class="btn-wrapper">
                                <a href="{{route(route_prefix().'admin.domain-reseller.index')}}"
                                   class="btn btn-outline-info btn-sm d-flex gap-2">
                                    <i class="mdi mdi-arrow-left menu-icon"></i>
                                    <span>{{__('Back')}}</span>
                                </a>
                            </div>
                            <div class="btn-wrapper">
                                <a href="{{route(route_prefix().'admin.domain-reseller.list.domain.failed')}}"
                                   class="btn btn-outline-danger btn-sm d-flex gap-2">
                                    <i class="mdi mdi-folder-remove menu-icon"></i>
                                    <span>{{tenant() ? __('Pending Purchases') : __('Failed Purchases')}}</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="body-wrap my-4">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th scope="col">{{__('# ID')}}</th>
                                <th scope="col">{{__('Tenant')}}</th>
                                <th scope="col">{{__('Domain')}}</th>
                                <th scope="col">{{__('Period')}}</th>
                                <th scope="col">{{__('Price')}}</th>
                                <th scope="col">{{__('Expire Date')}}</th>

                                @if(!!tenant())
                                    <th scope="col">{{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($domain_list ?? [] as $index => $item)
                                <tr>
                                    @php
                                        $custom_domain = \App\Models\CustomDomain::where(['custom_domain' => $item->domain, 'custom_domain_status' => 'connected'])->first();
                                    @endphp

                                    <th scope="row">{{$loop->iteration}}</th>
                                    <td>{{$item->paymentable_tenant?->id}}</td>
                                    <td>{{$item->domain}}</td>
                                    <td>{{$item->period}} {{__('Year')}}</td>
                                    <td>{{amount_with_currency_symbol($item->domain_price + $item->extra_fee)}}</td>
                                    <td>
                                        @php
                                            $date = \Carbon\Carbon::parse($item->expire_at ?? null);
                                            $isExpired = now() > $date;
                                        @endphp
                                        <p @class(['text-danger' => $isExpired, 'mb-0'])>{{$date->format('D, d F Y')}}</p>
                                        <p @class(['text-danger' => $isExpired, 'mb-0'])>
                                            <small
                                                class="text-capitalize">{{!$isExpired ? $date->diffForHumans(['parts' => 2]) : 'Expired'}}</small>
                                        </p>
                                    </td>

                                    @if(!!tenant())
                                        <td>
                                            @if(!$isExpired)
                                                @if($custom_domain)
                                                    <a href="javascript:void(0)" class="btn btn-danger btn-sm" disabled>
                                                        {{__('Activated')}}
                                                        <x-btn.button-loader class="d-none"/>
                                                    </a>
                                                @else
                                                    <a class="activate-btn btn btn-success btn-sm"
                                                       data-href="{{route('tenant.admin.domain-reseller.list.domain.activate')}}"
                                                       data-id="{{$item->id}}">
                                                        {{__('Activate')}}
                                                        <x-btn.button-loader class="d-none"/>
                                                    </a>
                                                @endif
                                            @else
                                                <a class="btn btn-info btn-sm"
                                                   href="{{route('tenant.admin.domain-reseller.renew', wrap_random_number($item->id))}}">
                                                    {{__('Renew Domain')}}
                                                    <x-btn.button-loader class="d-none"/>
                                                </a>
                                            @endif
                                        </td>
                                    @endif

                                </tr>
                            @empty
                                <tr>
                                    <td class="text-center" colspan="8">{{__('No data available')}}</td>
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
            "use strict";

            const loaderButton = (current, type = true) => {
                const button = $(current).find('span');

                if (type) {
                    button.removeClass('d-none')
                } else {
                    button.addClass('d-none')
                }
            };

            let unloadFlag = false;
            $(document).on('click', '.activate-btn', function (e) {
                e.preventDefault();

                unloadFlag = true;

                let el = $(this);
                let domain_id = el.attr('data-id');
                let url = el.attr('data-href');
                loaderButton(el);

                $.ajax({
                    type: 'GET',
                    url: `${url}?id=${domain_id}`,
                    beforeSend: function () {
                        showLoader();
                    },
                    success: function (response) {
                        if (response.status) {
                            hideLoader();
                            loaderButton(el, false);

                            showMessage('success', response.msg);
                            unloadFlag = false;
                        }
                    },
                    error: function (response) {
                        hideLoader();
                        loaderButton(el, false);
                        toastr.error(response.msg);
                        unloadFlag = false;
                    }
                })
            });

            $(window).bind('beforeunload', function () {
                if (unloadFlag) {
                    return `{{__('If you leave, the action will be terminated and system may malfunction. Are you sure?')}}`;
                }
            });

            const textLoader = ['Setting Up DNS', 'Configuring Custom Domain'];
            let currentIndex = 0;
            let timer = '';

            function loaderText(isRunning) {
                if (isRunning) {
                    timer = setTimeout(function () {
                        $('.loader-text').text(textLoader[currentIndex] + '..');
                        if (currentIndex < textLoader.length - 1) {
                            loaderText(true);
                        }
                        currentIndex++;
                    }, 1000)
                } else {
                    currentIndex = 0;
                    $('.loader-text').remove();
                    clearTimeout(timer);
                }
            }

            const showLoader = () => {
                $('body').css({position: 'relative'});
                $('.domain-reseller-loader-wrapper').css({display: 'block'});

                $('.loader-02').append('<h5 class="loader-text text-center">Processing..</h5>');
                loaderText(true);
            };

            const hideLoader = () => {
                $('body').css({position: ''});
                $('.domain-reseller-loader-wrapper').css({display: 'none'});
                loaderText(false);
            };

            const showMessage = (type, msg) => {
                if (type === 'success') {
                    toastr.success(msg);
                } else {
                    toastr.error(msg);
                }

                let body_wrap = $('.body-wrap');
                body_wrap.find('table').before(`
                    <p class="custom-alert alert alert-${type} mb-4">${msg}</p>
                `)

                setTimeout(() => {
                    body_wrap.find('.custom-alert').remove();
                }, 6000)
            }
        })(jQuery)
    </script>
@endsection
