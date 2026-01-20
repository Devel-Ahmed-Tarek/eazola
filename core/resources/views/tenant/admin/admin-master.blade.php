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
    .sidebar .nav .nav-item.active > .nav-link {
        background-color:rgb(154, 226, 145) !important;
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

<!-- Final Override - Force Black on Hover (Highest Priority) -->
@if(tenant())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Force black color on hover using JavaScript
        const tenantIcons = document.querySelectorAll('.tenant_info_icon');
        tenantIcons.forEach(function(icon) {
            icon.addEventListener('mouseenter', function() {
                this.style.color = '#000000 !important';
                const icons = this.querySelectorAll('i');
                icons.forEach(function(i) {
                    i.style.color = '#000000 !important';
                });
            });
            icon.addEventListener('mouseleave', function() {
                this.style.color = '#000000';
                const icons = this.querySelectorAll('i');
                icons.forEach(function(i) {
                    i.style.color = '#000000';
                });
            });
        });
    });
</script>
<style>
    /* Final Override - Maximum Specificity */
    body .navbar .tenant_info_item .tenant_info_icon:hover,
    body .navbar .tenant_info_item span.tenant_info_icon:hover,
    body .navbar .tenant_info_item div.tenant_info_icon:hover {
        color: #000000 !important;
    }
    body .navbar .tenant_info_item .tenant_info_icon:hover i,
    body .navbar .tenant_info_item .tenant_info_icon:hover i.mdi,
    body .navbar .tenant_info_item .tenant_info_icon:hover i.mdi-lightbulb-on-outline {
        color: #000000 !important;
    }
</style>
@endif
