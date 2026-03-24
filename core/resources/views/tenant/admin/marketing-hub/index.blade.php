@extends('tenant.admin.admin-master')
@section('title') {{ __('Marketing') }} @endsection

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
                            <i class="mdi mdi-bullhorn-outline"></i>
                        </span>
                        <div>
                            <h4 class="card-title mb-1">{{ __('Marketing') }}</h4>
                            <p class="content-hub-subtitle">
                                {{ __('Tools to capture leads, improve discovery, and communicate with your audience.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row content-hub-grid">
                    {{-- Form Builder --}}
                    <div class="col-md-4">
                        <a href="{{ route('tenant.admin.form.builder.all') }}" class="text-decoration-none text-reset">
                            <div class="card content-hub-card small-description h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag manage-tag">{{ __('Forms') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-form-textbox"></i>
                                        </span>
                                        <span>{{ __('Form Builder') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-0">
                                        {{ __('Create custom forms and view all submissions in one place.') }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- Newsletter Manage --}}
                    <div class="col-md-4">
                        <a href="{{ route('tenant.admin.newsletter') }}" class="text-decoration-none text-reset">
                            <div class="card content-hub-card small-description h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag manage-tag">{{ __('Email') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-email-newsletter"></i>
                                        </span>
                                        <span>{{ __('Newsletter Manage') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-0">
                                        {{ __('Manage subscribers and send campaigns to your newsletter list.') }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- SEO Settings --}}
                    <div class="col-md-4">
                        <a href="{{ route('tenant.admin.general.seo.settings') }}" class="text-decoration-none text-reset">
                            <div class="card content-hub-card small-description h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag manage-tag">{{ __('SEO') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-magnify"></i>
                                        </span>
                                        <span>{{ __('SEO Settings') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-0">
                                        {{ __('Global SEO meta tags and social sharing configuration.') }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- Sitemap Settings --}}
                    <div class="col-md-4">
                        <a href="{{ route('tenant.admin.general.sitemap.settings') }}" class="text-decoration-none text-reset">
                            <div class="card content-hub-card small-description h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag manage-tag">{{ __('SEO') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-sitemap"></i>
                                        </span>
                                        <span>{{ __('Sitemap Settings') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-0">
                                        {{ __('Generate and manage XML sitemaps for search engines.') }}
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

