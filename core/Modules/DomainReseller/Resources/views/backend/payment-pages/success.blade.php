@extends('tenant.admin.admin-master')

@section('title')
    {{ __('Order Successful - Domain Reseller Plugin') }}
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

                <div class="p-4 py-5 recent-order-wrapper dashboard-table bg-white padding-30">
                    <div class="wrapper">
                        <div class="header-wrap text-center">
                            <h2 class="header-title text-uppercase text-success mb-3">{{$order_details->purchase_count > 1 ? __("The order is renewed successfully") : __("The order is successful")}}</h2>
                            <h3>{{$order_details->domain .' - '. $order_details->period . ' Year'}}</h3>

                            <div class="card">
                                <div class="card-body py-4 pt-2 mt-3" style="background: #f8f8f8;">
                                    @php
                                        $statusText = '\Modules\DomainReseller\Http\Enums\StatusEnum::getText';
                                        $colors = [0 => 'text-danger', 1 => 'text-success']
                                    @endphp

                                    <table class="table">
                                        <tr>
                                            <th>
                                                <strong>{{__('Payment Status')}}</strong>
                                            </th>
                                            <th>
                                                <strong>{{__('Domain Status')}}</strong>
                                            </th>
                                        </tr>
                                        <tr>
                                            <td class="text-capitalize {{$colors[$order_details->payment_status]}}">{{$statusText($order_details->payment_status, true)}}</td>
                                            <td class="text-capitalize {{$colors[$order_details->status]}}">{{$statusText($order_details->status)}}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body py-2 mt-3" style="background: #f8f8f8;">
                                    <table class="table">
                                        <tr>
                                            <td>
                                                <strong>{{$order_details->domain .' - '. $order_details->period . ' Year'}}</strong>
                                            </td>
                                            <td>
                                                @php
                                                    $custom_domain = \App\Models\CustomDomain::where(['custom_domain' => $order_details->domain, 'custom_domain_status' => 'connected'])->exists();
                                                @endphp

                                                @if(!$custom_domain)
                                                    <a class="activate-btn btn btn-success btn-sm"
                                                       data-href="{{route('tenant.admin.domain-reseller.list.domain.activate')}}"
                                                       data-id="{{$order_details->id}}">
                                                        {{__('Activate')}} <x-btn.button-loader class="d-none"/>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                    <p class="mt-5">{{__('Now you can activate your newly purchased domain as your custom domain')}}</p>
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

                Swal.fire({
                    title: `{{__('Are you sure?')}}`,
                    text: `{{__("You won't be able to revert those!")}}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33333',
                    confirmButtonText: `{{__('Activate')}}`,
                    cancelButtonText: `{{__('Discard')}}`
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: 'GET',
                            url: `${url}?id=${domain_id}`,
                            beforeSend: function () {
                                showLoader();
                            },
                            success: function (response) {
                                if (response.status)
                                {
                                    hideLoader();
                                    loaderButton(el, false);

                                    showMessage('success', response.msg);
                                    unloadFlag = false;

                                    setTimeout(()=>{
                                        location.reload();
                                    }, 2000);
                                }
                            },
                            error: function (response) {
                                hideLoader();
                                loaderButton(el, false);
                                showMessage('danger', response.msg);
                                unloadFlag = false;
                            }
                        })
                    }
                })
            });

            $(window).bind('beforeunload', function(){
                if(unloadFlag)
                {
                    return `{{__('If you leave, the action will be terminated and system may malfunction. Are you sure?')}}`;
                }
            });

            const textLoader = ['Setting Up DNS','Configuring Custom Domain','Getting Ready'];
            let currentIndex = 0;
            let timer = '';

            function loaderText(isRunning) {
                if (isRunning) {
                    timer = setTimeout(function () {
                        $('.loader-text').text(textLoader[currentIndex]+'..');
                        if (currentIndex < textLoader.length-1) {
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
                if (type === 'success')
                {
                    toastr.success(msg);
                } else {
                    toastr.error(msg);
                }

                let body_wrap = $('.body-wrap');
                body_wrap.find('table').before(`
                    <p class="custom-alert alert alert-${type} mb-4">${msg}</p>
                `)

                setTimeout(()=>{
                    body_wrap.find('.custom-alert').remove();
                }, 6000)
            }
        })(jQuery)
    </script>
@endsection
