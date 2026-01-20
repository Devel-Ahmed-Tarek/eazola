@extends(route_prefix().'admin.admin-master')

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
            color: #333;
            background: #f3f3f3;
            position: absolute;
            top: 1rem;
            left: 1rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 10px;
            line-height: 18px;
            font-weight: 400;
        }

        .badge-exact-match.active-badge {
            background: #0d6efd;
            color: #fff;
        }

        .request-switch {
            position: absolute;
            top: 1rem;
            right: 1rem;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .why-great {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 15px;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
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
                            <h4 class="header-title mb-2">{{__("Purchase Domain")}}</h4>
                            <p>{{__('Find your perfect domain just searching domain name')}}</p>
                        </div>

                        <div>
                            <a href="{{route(route_prefix().'admin.domain-reseller.list.domain')}}"
                               class="btn btn-outline-info btn-sm d-flex gap-2">
                                <i class="mdi mdi-web menu-icon"></i>
                                <span>{{tenant() ? __('My Domains') : __('All Domains')}}</span>
                            </a>
                        </div>
                    </div>

                    <div class="body-wrap my-4 mt-5">
                        <form class="search-domain-form">
                            <div class="d-flex flex-wrap w-100">
                                <div class="form-group pr-0 domain-input-wrapper flex-grow-1">
                                    <input type="text" class="form-control" id="domain_name" name="domain_name"
                                           placeholder="{{__('Find your perfect domain')}}">
                                </div>

                                <div class="form-group domain-button-wrapper flex-shrink-0">
                                    <button class="btn btn-success rounded-0" type="submit">{{__('Search Domain')}}
                                        <x-btn.button-loader class="d-none"/>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="domain-availability-wrapper d-none"></div>
                    <div class="suggested-domains-wrapper d-none row g-4 mt-1"></div>
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

            $(document).on('click', 'form button[type=submit]', function () {
                loaderButton(this);
            });

            $(document).on('submit', 'form.search-domain-form', function (e) {
                e.preventDefault();

                let btn = $(this);
                let domain_el = $('#domain_name');
                let domain_text = domain_el.val().trim().toLowerCase();

                if (!domain_text) {
                    showErrorMessages(domain_el, `The field is required.`, btn);
                    return false;
                }
                hideErrorMessages(domain_el);

                $.ajax({
                    type: 'POST',
                    url: `{{route(route_prefix().'admin.domain-reseller.search.domain')}}`,
                    data: {
                        _token: `{{csrf_token()}}`,
                        domain_name: domain_text
                    },
                    beforeSend: function () {
                        $('form.search-domain-form button[type=submit]').attr('disabled', true);
                    },
                    success: function (response) {
                        if (!response.status) {
                            showErrorMessages(domain_el, response.message, btn);
                            checkException(response);
                            return false;
                        }

                        let domain_available = $('.domain-availability-wrapper');
                        domain_available.removeClass('d-none');

                        let domain = response.result.domain;
                        let lastDotPosition = domain.lastIndexOf('.');
                        let domainWithoutTld = domain.substring(0, lastDotPosition);

                        domain_available.html('');
                        if (response.result.available) {
                            let currency_symbol = response.result.currency === 'USD' ? '$' : response.result.currency;
                            let price = response.result.price;

                            domain_available.html(`
                                <p class="text-success">${response.result.domain} is available</p>
                                <div class="domain-available offer-card position-relative">
                                    <span class="badge badge-exact-match active-badge">{{__('EXACT MATCH')}}</span>
                                    <div class="form-check form-switch request-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="request-privacy" checked>
                                        <label class="form-check-label m-0" for="request-privacy">Request privacy for the domain</label>
                                    </div>

                                    <h1 class="searched-domain mt-4 mb-2">${response.result.domain}</h1>
                                    <div class="prices">
                                        <p class="sale-price mb-2">${currency_symbol + price}</p>
                                    </div>
                                    <p class="text-muted">Validity ${response.result.period} Year</p>
                                    <button class="buy-button btn btn-primary mt-3" data-domain="${response.result.domain}">Make It Yours <span class="loading-icon mdi mdi-spin mdi-loading d-none"></span></button>
                                    <div class="why-great mt-4">
                                        <i class="mdi mdi-lightbulb-on-outline"></i>
                                        Why it's great: "${domainWithoutTld}" is ${domainWithoutTld.length} characters or less.
                                    </div>
                                </div>
                            `);
                        } else {
                            domain_available.html(`
                                <p class="text-danger">${response.result.domain} is not available</p>
                                <div class="domain-available offer-card position-relative">
                                    <span class="badge badge-exact-match active-badge">{{__('EXACT MATCH')}}</span>
                                    <h1 class="searched-domain mt-4 mb-2">${response.result.domain}</h2>
                                    <h3>The domain is not available</h3>
                                    <div class="why-great mt-4">
                                        "${domainWithoutTld}" is ${domainWithoutTld.length} characters or less.
                                    </div>
                                </div>
                            `);
                        }

                        loaderButton(btn, false);
                        $('form.search-domain-form button[type=submit]').attr('disabled', false);

                        let isAuthorized = `{{empty(tenant())}}`; // empty = true = landlord = not authorized
                        if (isAuthorized) {
                            showErrorMessages(domain_el, `This service is only available for tenants (shops), You can use this for test purpose and can not do further actions.`, btn);
                            $(".domain-available *").attr("disabled", true).attr('data-domain', '').off('click');
                        }
                    },
                    error: function (response) {
                        console.log(response)
                        console.log('Error');
                        loaderButton(btn, false);
                    }
                })
            })

            const checkException = (response) => {
                if (response.exception) {
                    let list = response.suggestion;
                    let suggested_domains_wrapper = $('.suggested-domains-wrapper');
                    suggested_domains_wrapper.removeClass('d-none');

                    list.forEach((item) => {
                        suggested_domains_wrapper.append(`
                                <div class="col-sm-6 col-lg-4 col-xl-3">
                                    <div class="domain-available domain-suggestion offer-card position-relative">
                                        <span class="badge badge-exact-match">{{__('SUGGESTIVE MATCH')}}</span>
                                        <h2 class="searched-domain mt-4 mb-2">${item.domain}</h2>
                                        <div class="why-great mt-4">
                                            "${item.domain}" is ${item.domain.length} characters or less.
                                        </div>
                                    </div>
                                </div>
                        `);
                    })
                }
            }

            const showErrorMessages = (selector_obj, message, btn_obj) => {
                selector_obj.addClass('is-invalid');
                selector_obj.siblings('.invalid-feedback').remove();
                selector_obj.after(`
                        <small class='invalid-feedback'>${message}</small>
                    `)
                toastr.error(message);
                loaderButton(btn_obj, false);
                $('form.search-domain-form button[type=submit]').attr('disabled', false);
                return false;
            }

            const hideErrorMessages = (selector_obj) => {
                selector_obj.removeClass('is-invalid');
                selector_obj.siblings('.invalid-feedback').remove();
            }

            $(document).on('click', '.buy-button', function () {
                let el = $(this);
                let domain_name = el.attr('data-domain').trim();
                let privacy_request = el.siblings('.request-switch').find('#request-privacy').is(':checked');

                if (domain_name !== "" && domain_name !== undefined && isNaN(domain_name)) {
                    $.ajax({
                        type: 'POST',
                        url: `{{route('tenant.admin.domain-reseller.select.domain')}}`,
                        data: {
                            _token: `{{csrf_token()}}`,
                            domain_name,
                            privacy_request,
                        },
                        beforeSend: function () {
                            el.find('.loading-icon').removeClass('d-none');
                        },
                        success: function (response) {
                            if (response.status) {
                                setTimeout(() => {
                                    location.href = response.url;
                                }, 2000);
                            } else {
                                toastr.error('Something went wrong');
                            }
                        }
                    });
                }
            });
        })(jQuery)
    </script>
@endsection
