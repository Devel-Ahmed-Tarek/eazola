@include('landlord.admin.partials.header')

<!-- Additional Override for Inline Styles (After Topbar) -->
<style>
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
    .navbar-nav .count-indicator .mdi-bell,
    .navbar-nav .count-indicator .mdi-bell-outline,
    .navbar-nav .nav-logout .mdi,
    .navbar-nav .nav-logout .mdi-upload,
    .navbar-dropdown .dropdown-item i,
    .navbar-dropdown .dropdown-item .mdi {
        color: #000000 !important;
    }
    /* All Buttons in Header/Navbar - Black Text Always */
    .navbar .btn,
    .navbar .btn-info,
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
    .navbar .btn-info:not(:hover),
    .navbar .btn-outline-danger:not(:hover),
    .navbar-nav .nav-logout .btn:not(:hover) {
        color: #000000 !important;
    }
    .navbar .btn:hover,
    .navbar .btn-info:hover,
    .navbar .btn-outline-danger:hover,
    .navbar-nav .nav-logout .btn:hover,
    .navbar .btn:hover span,
    .navbar .btn:hover i {
        color: #000000 !important;
    }
    /* Active Sidebar Link - Override Purple to Black */
    .sidebar .nav .nav-item.active > .nav-link .menu-title,
    .sidebar .nav .nav-item.active > .nav-link i {
        color: #000000 !important;
    }
    .sidebar .nav .nav-item.active > .nav-link {
        background-color:rgb(154, 226, 145) !important;
    }
</style>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title"> @yield('title') </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('landlord.admin.home')}}">{{__('Dashboard')}}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                </ol>
            </nav>
        </div>
        @yield('content')
    </div>

 @include('landlord.admin.partials.footer')
