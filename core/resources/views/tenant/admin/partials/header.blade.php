<!doctype html>
<html lang="{{ \App\Facades\GlobalLanguage::default_slug() }}" dir="{{ \App\Facades\GlobalLanguage::default_dir() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @if(!request()->routeIs('tenant.home'))
            @yield('title')  -
        @endif
        {{get_static_option('site_'.\App\Facades\GlobalLanguage::user_lang_slug().'_title',__('Xgenious'))}}
        @if(!empty(get_static_option('site_'.\App\Facades\GlobalLanguage::user_lang_slug().'_tag_line')))
            - {{get_static_option('site_'.\App\Facades\GlobalLanguage::user_lang_slug().'_tag_line')}}
        @endif
    </title>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    {!! render_favicon_by_id(get_static_option('site_favicon')) !!}
    <!-- Styles -->
    <link href="{{ global_asset('assets/landlord/admin/css/materialdesignicons.min.css') }}" rel="stylesheet">
    <link href="{{ global_asset('assets/landlord/admin/css/vendor.bundle.base.css') }}" rel="stylesheet">
    <link href="{{ global_asset('assets/landlord/admin/css/style.css') }}" rel="stylesheet">
    <link href="{{ global_asset('assets/common/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ global_asset('assets/common/css/flatpickr.min.css') }}" rel="stylesheet">
    <link href="{{ global_asset('assets/common/css/fontawesome-iconpicker.min.css') }}" rel="stylesheet">
    <link href="{{ global_asset('assets/common/css/line-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ global_asset('assets/landlord/admin/css/nice-select.css') }}" rel="stylesheet">
    <link href="{{ global_asset('assets/common/css/toastr.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{global_asset('assets/common/css/flatpickr.min.css')}}">
    <link href="{{ global_asset('assets/common/css/fontawesome-iconpicker.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{global_asset('assets/common/css/custom-style.css')}}">

    <!-- Eazola Tenant Admin Custom Override -->
    {{-- @if(tenant()) --}}
        <link href="{{ global_asset('assets/tenant/admin/css/tenant-admin-override.css') }}" rel="stylesheet">
        <!-- Additional Override for Inline Styles -->
        <style>
            /* Override for tenant_info_icon purple color */
            .tenant_info_icon,
            span.tenant_info_icon,
            div.tenant_info_icon,
            a.tenant_info_icon {
                color: #000000 !important;
            }
            .tenant_info_icon i,
            .tenant_info_icon i.mdi,
            .tenant_info_icon i.mdi-lightbulb-on-outline {
                color: #000000 !important;
            }
            .tenant_info_icon:hover,
            span.tenant_info_icon:hover,
            div.tenant_info_icon:hover,
            .navbar .tenant_info_icon:hover {
                background-color: rgba(46, 204, 113, 0.1) !important;
                color: #000000 !important;
            }
            .tenant_info_icon:hover i,
            .tenant_info_icon:hover i.mdi,
            .tenant_info_icon:hover i.mdi-lightbulb-on-outline,
            span.tenant_info_icon:hover i,
            div.tenant_info_icon:hover i,
            .navbar .tenant_info_icon:hover i {
                color: #000000 !important;
            }
            /* Override any purple color inline styles */
            [style*="#b66dff"],
            [style*="color: #b66dff"],
            [style*="color:#b66dff"] {
                color: #2ECC71 !important;
            }
            [style*="background-color: #b66dff"],
            [style*="background:#b66dff"] {
                background-color: #2ECC71 !important;
            }
            /* All Icons in Header/Navbar - Black Color */
            .navbar i,
            .navbar .mdi,
            .navbar-toggler i.mdi,
            .navbar-toggler .mdi-menu,
            .navbar-nav .nav-link i,
            .navbar-nav .nav-link .mdi,
            .navbar-nav .nav-item .nav-link i,
            .navbar-nav .count-indicator i,
            .navbar-nav .count-indicator .mdi-email-outline,
            .navbar-nav .count-indicator .mdi-bell-outline,
            .navbar-nav .nav-logout .mdi,
            .navbar-nav .nav-logout .mdi-upload,
            .navbar-dropdown .dropdown-item i,
            .navbar-dropdown .dropdown-item .mdi {
                color: #000000 !important;
            }
            /* All Buttons in Header/Navbar - Black Text Always */
            .navbar .btn,
            .navbar .btn-outline-danger,
            .navbar .btn-icon-text,
            .navbar-nav .nav-logout .btn,
            .navbar-nav .nav-logout .btn-outline-danger,
            .navbar-nav .nav-logout .btn-icon-text,
            .navbar .btn-outline-danger.btn-icon-text,
            .navbar .btn .btn-icon-prepend,
            .navbar .btn span,
            .navbar .btn i {
                color: #000000 !important;
            }
            .navbar .btn:not(:hover),
            .navbar .btn-outline-danger:not(:hover),
            .navbar-nav .nav-logout .btn:not(:hover) {
                color: #000000 !important;
            }
            .navbar .btn:hover,
            .navbar .btn-outline-danger:hover,
            .navbar-nav .nav-logout .btn:hover,
            .navbar .btn:hover span,
            .navbar .btn:hover i {
                color: #000000 !important;
            }
            /* Plugin Menu Badge - Purple #D286FF to Green */
            .sidebar .nav .nav-item .nav-link .menu-title.plugin-menu:before {
                background-color: #2ECC71 !important;
            }
            .sidebar .nav .nav-item.plugin-menu::before,
            .sidebar .nav .nav-item.submenu-item::before,
            .sidebar .nav .nav-item.submenu-item.plugin-menu::before,
            li.nav-item.plugin-menu::before,
            li.nav-item.submenu-item::before,
            li.nav-item.submenu-item.plugin-menu::before,
            li.nav-item.submenu-item-domain-reseller-menu.plugin-menu::before {
                background-color: #2ECC71 !important;
                background: #2ECC71 !important;
            }
        </style>
    {{-- @endif --}}

    @if(!empty(get_static_option('dark_mode_for_admin_panel')))
        <link href="{{ global_asset('assets/landlord/admin/css/dark-mode.css') }}" rel="stylesheet">
    @endif

    @if(\App\Facades\GlobalLanguage::default_dir() === 'rtl')
        <link href="{{ global_asset('assets/landlord/admin/css/rtl.css') }}" rel="stylesheet">
    @endif


    <!-- dark mode css  -->
    @yield('style')


</head>
<body>

<div class="container-scroller">
@include('tenant.admin.partials.topbar')
    <div class="container-fluid page-body-wrapper">
@include('tenant.admin.partials.sidebar')
