@extends(route_prefix().'admin.admin-master')

@section('title')
    {{ __('Site Analytics') }}
@endsection

@section('style')

@endsection

@section('content')
    @if(!extension_loaded('intl'))
        <div class="alert alert-danger">
            <p>{{__('The plugin requires PHP intl extension to run it properly. Kindly enable the extension on your server.')}}</p>
        </div>
    @endif

    <div class="dashboard-recent-order">
        <div class="row">
            <x-flash-msg/>
            <x-error-msg/>
            <div class="col-md-12">
                <div class="p-4 recent-order-wrapper dashboard-table bg-white padding-30">
                    <div class="wrapper d-flex justify-content-between">
                        <div class="header-wrap">
                            <h4 class="header-title mb-2">{{__("Site Analytics Settings")}}</h4>
                            <p>{{__("Manage site analytics and views from here, you can active/deactivate the usages from here.")}}</p>
                        </div>
                    </div>

                    <form action="{{route(route_prefix().'admin.analytics.settings')}}" method="POST">
                        @csrf
                        <x-fields.switcher class="site_analytics_status_btn" label="Enable or disable site analytics" name="site_analytics_status" value="{{get_static_option('site_analytics_status')}}"/>
                        <div @class(["settings_wrapper", "d-none" => empty(get_static_option('site_analytics_status'))])>
                            <hr>
                            <h4 class="mt-4">{{__('Overall')}}</h4>
                            <div class="d-flex gap-5">
                                <x-fields.switcher label="Show or hide unique users" name="site_analytics_unique_user" value="{{get_static_option('site_analytics_unique_user')}}"/>
                                <x-fields.switcher label="Show or hide page views" name="site_analytics_page_view" value="{{get_static_option('site_analytics_page_view')}}"/>
                                <x-fields.switcher label="Show or hide page source" name="site_analytics_view_source" value="{{get_static_option('site_analytics_view_source')}}"/>
                                <x-fields.switcher label="Show or hide users country" name="site_analytics_users_country" value="{{get_static_option('site_analytics_users_country')}}"/>
                                <x-fields.switcher label="Show or hide users device" name="site_analytics_users_device" value="{{get_static_option('site_analytics_users_device')}}"/>
                            </div>


                            @if(tenant())
                                <h4 class="mt-2">{{__('Products')}}</h4>
                                <div class="d-flex gap-5">
                                    <x-fields.switcher label="Show or hide most viewed products" name="site_analytics_most_viewed_products" value="{{get_static_option('site_analytics_most_viewed_products')}}"/>
                                    <x-fields.switcher label="Show or hide purchase bounce rate" name="site_analytics_purchase_bounce_rate" value="{{get_static_option('site_analytics_purchase_bounce_rate')}}"/>
                                </div>
                            @endif
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">{{__('Update')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $(document).on('change', 'input[name=site_analytics_status]', function () {
                let el = $(this);
                let value = el.is(':checked');

                if (!value)
                {
                    let all_checkbox = $('.settings_wrapper input[type=checkbox]');
                    $.each(all_checkbox, function (key, value) {
                        $(value).prop('checked', false)
                    });
                }
                $('.settings_wrapper').toggleClass('d-none');
            });
        });
    </script>
@endsection
