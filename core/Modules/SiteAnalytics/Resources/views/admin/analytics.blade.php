@extends(route_prefix().'admin.admin-master')

@section('title')
    {{ __('Site Analytics Dashboard') }}
@endsection

@section('style')
    <style>
        .card-header {
            background-color: rgba(249, 250, 251, 1);
        }

        .card-header p {
            font-size: .75rem;
        }

        .apexcharts-canvas {
            margin-inline: auto;
        }

        .pagesFav {
            object-fit: contain;
        }

        .recent-orderChart {
            height: 100%;
        }

        a {
            text-decoration: none;
            color: var(--bs-dark);
        }
    </style>
@endsection

@section('content')
    <div class="dashboard-recent-order">
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-end">
                    @include('siteanalytics::admin.data.filter', ['type' => 'analytics'])
                </div>
            </div>
        </div>

        <div class="dashboard-recent-order">
            <div class="row g-4 mt-1">
                <x-flash-msg/>
                <x-error-msg/>
                <div class="col-md-6">
                    <div class="p-4 recent-order-wrapper recent-orderChart bg-white">
                        <div class="wrapper d-flex justify-content-between">
                            <div class="header-wrap">
                                <h4 class="header-title mb-2 text-capitalize">{{tenant() ? __("all product views") : __("all subscription plan views")}}</h4>
                            </div>
                        </div>
                        <div class="page-view chart-wrapper">
                            <div class="my-2" id="chart-total"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="p-4 recent-order-wrapper recent-orderChart bg-white">
                        <div class="wrapper d-flex justify-content-between">
                            <div class="header-wrap">
                                <h4 class="header-title mb-2 text-capitalize">{{__("locations and devices")}}</h4>
                            </div>
                        </div>
                        <div class="page-view chart-wrapper">
                            <div id="chart-country"></div>
                            <div id="chart-device"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-1 row g-4 mb-5">
            <div class="col-lg-6">
                @includeWhen(empty(tenant()) && get_static_option('site_analytics_page_view') ,'siteanalytics::admin.data.plan-card')
                @includeWhen(!empty(tenant()) && get_static_option('site_analytics_most_viewed_products') ,'siteanalytics::admin.data.product-card')
            </div>
            <div class="col-lg-6">
                @includeWhen(!empty(get_static_option('site_analytics_view_source')) ,'siteanalytics::admin.data.sources-card')
            </div>
            <div class="col-lg-6">
                @includeWhen(!empty(get_static_option('site_analytics_users_country')) ,'siteanalytics::admin.data.users-card')
            </div>
            <div class="col-lg-6">
                @includeWhen(!empty(get_static_option('site_analytics_users_device')) ,'siteanalytics::admin.data.devices-card')
            </div>
        </div>
        @endsection

        @section('scripts')
            <script src="{{global_asset('assets/landlord/admin/js/apexcharts.js')}}"></script>

            @includeWhen(empty(tenant()) ,'siteanalytics::admin.partials.landlord.analytics-charts-js')
            @includeWhen(!empty(tenant()) ,'siteanalytics::admin.partials.tenant.analytics-charts-js')
        @endsection
