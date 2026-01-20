@include('tenant.admin.partials.header')

<!-- Additional Override for Inline Styles (After Topbar) -->
@if(tenant())
<style>
    /* Override for tenant_info_icon purple color - High Priority */
    .tenant_info_icon,
    span.tenant_info_icon,
    div.tenant_info_icon,
    a.tenant_info_icon {
        color: #2ECC71 !important;
    }
    .tenant_info_icon i,
    .tenant_info_icon i.mdi,
    .tenant_info_icon i.mdi-lightbulb-on-outline {
        color: #2ECC71 !important;
    }
    .tenant_info_icon:hover {
        background-color: rgba(46, 204, 113, 0.1) !important;
        color: #27AE60 !important;
    }
    .tenant_info_icon:hover i {
        color: #27AE60 !important;
    }
    /* Override any purple color inline styles */
    [style*="#b66dff"],
    [style*="color: #b66dff"],
    [style*="color:#b66dff"],
    [style*="color: #b66dff !important"],
    [style*="color:#b66dff !important"] {
        color: #2ECC71 !important;
    }
    [style*="background-color: #b66dff"],
    [style*="background:#b66dff"],
    [style*="background-color: #b66dff !important"] {
        background-color: #2ECC71 !important;
    }
    /* Active Sidebar Link - Override Purple to Black */
    .sidebar .nav .nav-item.active > .nav-link .menu-title,
    .sidebar .nav .nav-item.active > .nav-link i {
        color: #000000 !important;
    }
</style>
@endif

<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title"> @yield('title') </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                </ol>
            </nav>
        </div>
        @yield('content')
    </div>

 @include('tenant.admin.partials.footer')

