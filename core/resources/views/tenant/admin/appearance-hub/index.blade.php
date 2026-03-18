@extends('tenant.admin.admin-master')
@section('title') {{ __('Appearance Settings') }} @endsection

@section('style')
    <style>
        .content-hub-wrapper {
            background: linear-gradient(135deg, #f5f7fb 0%, #ffffff 60%, #e9f7ef 100%);
            border-radius: 16px;
            padding: 24px 24px 8px;
        }
        .content-hub-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 24px;
        }
        .content-hub-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .content-hub-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 999px;
            background: rgba(46, 204, 113, 0.12);
            color: #2ecc71;
            font-size: 18px;
        }
        .content-hub-subtitle {
            font-size: 13px;
            color: #6c757d;
            margin: 0;
        }
        .content-hub-grid {
            row-gap: 18px;
        }
        .content-hub-card {
            border-radius: 14px;
            border: 1px solid rgba(0,0,0,0.03);
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
            transition: all 0.18s ease-in-out;
            height: 100%;
        }
        .content-hub-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.09);
            border-color: rgba(46, 204, 113, 0.5);
        }
        .content-hub-card .card-body {
            padding: 16px 16px 14px;
        }
        .content-hub-card .card-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .content-hub-icon {
            width: 26px;
            height: 26px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            background: rgba(0,0,0,0.04);
            color: #2ecc71;
        }
        .content-hub-card.small-description {
            min-height: 120px;
        }
        .content-hub-card p.card-text {
            font-size: 12px;
        }
        .content-hub-tag {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 500;
            background: rgba(108,117,125,0.08);
            color: #6c757d;
            margin-bottom: 6px;
        }
        .content-hub-tag.manage-tag {
            background: rgba(241,196,15,0.08);
            color: #f1c40f;
        }
        .content-hub-links a {
            font-size: 13px;
            border-radius: 6px;
            padding-inline: 6px;
        }
        .content-hub-links a:hover {
            background: rgba(46, 204, 113, 0.08);
        }
        .content-hub-accordion .accordion-button {
            font-size: 12px;
            padding: 6px 10px;
            border-radius: 999px !important;
            background-color: #f8fafc;
        }
        .content-hub-accordion .accordion-button:focus {
            box-shadow: none;
        }
        .content-hub-accordion .accordion-button:not(.collapsed) {
            background-color: rgba(46, 204, 113, 0.09);
            color: #111827;
        }
        .content-hub-accordion .accordion-body {
            padding-inline: 0;
            padding-bottom: 0;
        }
        @media (max-width: 991.98px) {
            .content-hub-wrapper {
                padding: 18px 14px 6px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="content-hub-wrapper">
                <div class="content-hub-header">
                    <div class="content-hub-title">
                        <span class="content-hub-pill">
                            <i class="mdi mdi-palette-outline"></i>
                        </span>
                        <div>
                            <h4 class="card-title mb-1">{{ __('Appearance Settings') }}</h4>
                            <p class="content-hub-subtitle">
                                {{ __('Control themes, menus, widgets and system pages appearance from one place.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row content-hub-grid">
                    {{-- Pages (first card) --}}
                    <div class="col-md-4">
                        <a href="{{ route('tenant.admin.pages') }}" class="text-decoration-none text-reset">
                            <div class="card content-hub-card small-description h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag manage-tag">{{ __('Pages') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-file-document-multiple"></i>
                                        </span>
                                        <span>{{ __('All Pages') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-0">
                                        {{ __('Create and manage static pages used across your tenant site.') }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- Theme Manage --}}
                    <div class="col-md-4">
                        <a href="{{ route('tenant.admin.theme') }}" class="text-decoration-none text-reset">
                            <div class="card content-hub-card small-description h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag manage-tag">{{ __('Theme') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-view-carousel"></i>
                                        </span>
                                        <span>{{ __('Theme Manage') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-0">
                                        {{ __('Choose and configure the active tenant theme with demo data options.') }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- Page Settings (shortcut from General) --}}
                    <div class="col-md-4">
                        <a href="{{ route('tenant.admin.general.page.settings') }}" class="text-decoration-none text-reset">
                            <div class="card content-hub-card small-description h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag manage-tag">{{ __('Pages') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-file-document-outline"></i>
                                        </span>
                                        <span>{{ __('Page Settings') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-0">
                                        {{ __('Control default home page and global page display options.') }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- Site Identity (shortcut from General) --}}
                    <div class="col-md-4">
                        <a href="{{ route('tenant.admin.general.site.identity') }}" class="text-decoration-none text-reset">
                            <div class="card content-hub-card small-description h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag manage-tag">{{ __('Brand') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-shield-check-outline"></i>
                                        </span>
                                        <span>{{ __('Site Identity') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-0">
                                        {{ __('Logo, favicon and core brand information used in appearance.') }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- Color Settings (shortcut from General) --}}
                    <div class="col-md-4">
                        <a href="{{ route('tenant.admin.general.color.settings') }}" class="text-decoration-none text-reset">
                            <div class="card content-hub-card small-description h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag manage-tag">{{ __('Design') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-palette"></i>
                                        </span>
                                        <span>{{ __('Color Settings') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-0">
                                        {{ __('Primary and secondary color palette for the tenant theme.') }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- Typography Settings (shortcut from General) --}}
                    <div class="col-md-4">
                        <a href="{{ route('tenant.admin.general.typography.settings') }}" class="text-decoration-none text-reset">
                            <div class="card content-hub-card small-description h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag manage-tag">{{ __('Fonts') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-format-size"></i>
                                        </span>
                                        <span>{{ __('Typography Settings') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-0">
                                        {{ __('Fonts and sizes for headings and body text used in appearance.') }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- Menu Manage --}}
                    <div class="col-md-4">
                        <a href="{{ route('tenant.admin.menu') }}" class="text-decoration-none text-reset">
                            <div class="card content-hub-card small-description h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag manage-tag">{{ __('Navigation') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-view-list"></i>
                                        </span>
                                        <span>{{ __('Menu Manage') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-0">
                                        {{ __('Create and arrange navigation menus used on your tenant site.') }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- Widget Builder --}}
                    <div class="col-md-4">
                        <a href="{{ route('tenant.admin.widgets') }}" class="text-decoration-none text-reset">
                            <div class="card content-hub-card small-description h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag manage-tag">{{ __('Widgets') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-view-grid-plus"></i>
                                        </span>
                                        <span>{{ __('Widget Builder') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-0">
                                        {{ __('Configure widget areas and components for different pages.') }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- Topbar Settings --}}
                    <div class="col-md-4">
                        <a href="{{ route('tenant.admin.topbar.settings') }}" class="text-decoration-none text-reset">
                            <div class="card content-hub-card small-description h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag manage-tag">{{ __('Header') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-page-layout-header"></i>
                                        </span>
                                        <span>{{ __('Topbar Settings') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-0">
                                        {{ __('Control tenant topbar content such as contact info and social links.') }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- Other Settings --}}
                    <div class="col-md-4">
                        <a href="{{ route('tenant.admin.other.settings') }}" class="text-decoration-none text-reset">
                            <div class="card content-hub-card small-description h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag manage-tag">{{ __('Other') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-tune-variant"></i>
                                        </span>
                                        <span>{{ __('Other Settings') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-0">
                                        {{ __('Extra appearance related options depending on active theme.') }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- 404 Settings --}}
                    <div class="col-md-4">
                        <a href="{{ route('tenant.admin.404.page.settings') }}" class="text-decoration-none text-reset">
                            <div class="card content-hub-card small-description h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag manage-tag">{{ __('System Page') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-alert-circle"></i>
                                        </span>
                                        <span>{{ __('404 Page Settings') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-0">
                                        {{ __('Customize the 404 not found page texts and image.') }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- Maintenance Settings --}}
                    <div class="col-md-4">
                        <a href="{{ route('tenant.admin.maintains.page.settings') }}" class="text-decoration-none text-reset">
                            <div class="card content-hub-card small-description h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag manage-tag">{{ __('System Page') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-tools"></i>
                                        </span>
                                        <span>{{ __('Maintenance Settings') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-0">
                                        {{ __('Configure maintenance page content and coming-back date.') }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

