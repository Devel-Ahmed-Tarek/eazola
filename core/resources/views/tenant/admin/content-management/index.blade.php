@extends('tenant.admin.admin-master')
@section('title') {{ __('Content Management') }} @endsection

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
        .content-hub-tag.list-tag {
            background: rgba(52,152,219,0.08);
            color: #3498db;
        }
        .content-hub-tag.single-tag {
            background: rgba(155,89,182,0.08);
            color: #9b59b6;
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
                            <i class="mdi mdi-view-dashboard-outline"></i>
                        </span>
                        <div>
                            <h4 class="card-title mb-1">{{ __('Content Management') }}</h4>
                            <p class="content-hub-subtitle">
                                {{ __('Quick access to all reusable content modules in your tenant.') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="row content-hub-grid">
                        {{-- Blogs (LIST) --}}
                        <div class="col-md-4">
                            <div class="card content-hub-card h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag list-tag">{{ __('List module') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-note-outline"></i>
                                        </span>
                                        <span>{{ __('Blogs') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-2">{{ __('Manage all blog content') }}</p>
                                    <div class="accordion content-hub-accordion" id="blogsAccordion">
                                        <div class="accordion-item border-0">
                                            <h2 class="accordion-header" id="headingBlogs">
                                                <button class="accordion-button p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBlogs" aria-expanded="true" aria-controls="collapseBlogs">
                                                    {{ __('Open list') }}
                                                </button>
                                            </h2>
                                            <div id="collapseBlogs" class="accordion-collapse collapse show" aria-labelledby="headingBlogs" data-bs-parent="#blogsAccordion">
                                                    <div class="accordion-body p-2">
                                                        <ul class="list-unstyled mb-0 content-hub-links">
                                                            <li><a href="{{ route('tenant.admin.blog') }}" class="d-block py-1">{{ __('All Blogs') }}</a></li>
                                                            <li><a href="{{ route('tenant.admin.blog.new') }}" class="d-block py-1">{{ __('Add New Blog') }}</a></li>
                                                            <li><a href="{{ route('tenant.admin.blog.category') }}" class="d-block py-1">{{ __('Blog Category') }}</a></li>
                                                            <li><a href="{{ route('tenant.admin.blog.settings') }}" class="d-block py-1">{{ __('Blog Settings') }}</a></li>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Services (LIST) --}}
                        <div class="col-md-4">
                            <div class="card content-hub-card h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag list-tag">{{ __('List module') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-briefcase-outline"></i>
                                        </span>
                                        <span>{{ __('Services') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-2">{{ __('Manage all services') }}</p>
                                    <div class="accordion content-hub-accordion" id="servicesAccordion">
                                        <div class="accordion-item border-0">
                                            <h2 class="accordion-header" id="headingServices">
                                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseServices" aria-expanded="false" aria-controls="collapseServices">
                                                    {{ __('Open list') }}
                                                </button>
                                            </h2>
                                            <div id="collapseServices" class="accordion-collapse collapse" aria-labelledby="headingServices" data-bs-parent="#servicesAccordion">
                                                <div class="accordion-body p-2">
                                                    <ul class="list-unstyled mb-0 content-hub-links">
                                                        <li><a href="{{ route('tenant.admin.service') }}" class="d-block py-1">{{ __('All Services') }}</a></li>
                                                        <li><a href="{{ route('tenant.admin.service.add') }}" class="d-block py-1">{{ __('Add Service') }}</a></li>
                                                        <li><a href="{{ route('tenant.admin.service.category') }}" class="d-block py-1">{{ __('Service Category') }}</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Knowledgebase (LIST) --}}
                        <div class="col-md-4">
                            <div class="card content-hub-card h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag list-tag">{{ __('List module') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-book-open-variant"></i>
                                        </span>
                                        <span>{{ __('Knowledgebase') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-2">{{ __('Manage articles and categories') }}</p>
                                    <div class="accordion content-hub-accordion" id="kbAccordion">
                                        <div class="accordion-item border-0">
                                            <h2 class="accordion-header" id="headingKb">
                                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKb" aria-expanded="false" aria-controls="collapseKb">
                                                    {{ __('Open list') }}
                                                </button>
                                            </h2>
                                            <div id="collapseKb" class="accordion-collapse collapse" aria-labelledby="headingKb" data-bs-parent="#kbAccordion">
                                                <div class="accordion-body p-2">
                                                    <ul class="list-unstyled mb-0 content-hub-links">
                                                        <li><a href="{{ route('tenant.admin.knowledgebase') }}" class="d-block py-1">{{ __('All Articles') }}</a></li>
                                                        <li><a href="{{ route('tenant.admin.knowledgebase.new') }}" class="d-block py-1">{{ __('Add Article') }}</a></li>
                                                        <li><a href="{{ route('tenant.admin.knowledgebase.category') }}" class="d-block py-1">{{ __('Knowledgebase Category') }}</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- FAQs (LIST) --}}
                        <div class="col-md-4">
                            <div class="card content-hub-card h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag list-tag">{{ __('List module') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-help-circle-outline"></i>
                                        </span>
                                        <span>{{ __('FAQs') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-2">{{ __('Frequently asked questions') }}</p>
                                    <div class="accordion content-hub-accordion" id="faqAccordion">
                                        <div class="accordion-item border-0">
                                            <h2 class="accordion-header" id="headingFaq">
                                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFaq" aria-expanded="false" aria-controls="collapseFaq">
                                                    {{ __('Open list') }}
                                                </button>
                                            </h2>
                                            <div id="collapseFaq" class="accordion-collapse collapse" aria-labelledby="headingFaq" data-bs-parent="#faqAccordion">
                                                <div class="accordion-body p-2">
                                                    <ul class="list-unstyled mb-0 content-hub-links">
                                                        <li><a href="{{ route('tenant.admin.faq') }}" class="d-block py-1">{{ __('All FAQ') }}</a></li>
                                                        <li><a href="{{ route('tenant.admin.faq.category') }}" class="d-block py-1">{{ __('FAQ Category') }}</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Testimonials (single card) --}}
                        <div class="col-md-4">
                            <a href="{{ route('tenant.admin.testimonial') }}" class="text-decoration-none text-reset">
                                <div class="card content-hub-card small-description h-100">
                                    <div class="card-body">
                                        <span class="content-hub-tag single-tag">{{ __('Single module') }}</span>
                                        <h5 class="card-title">
                                            <span class="content-hub-icon">
                                                <i class="mdi mdi-format-quote-close"></i>
                                            </span>
                                            <span>{{ __('Testimonials') }}</span>
                                        </h5>
                                        <p class="card-text small text-muted mb-0">{{ __('Manage client testimonials') }}</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        {{-- Image Gallery (LIST) --}}
                        <div class="col-md-4">
                            <div class="card content-hub-card h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag list-tag">{{ __('List module') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-image-multiple"></i>
                                        </span>
                                        <span>{{ __('Image Gallery') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-2">{{ __('Manage gallery images and categories') }}</p>
                                    <div class="accordion content-hub-accordion" id="galleryAccordion">
                                        <div class="accordion-item border-0">
                                            <h2 class="accordion-header" id="headingGallery">
                                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGallery" aria-expanded="false" aria-controls="collapseGallery">
                                                    {{ __('Open list') }}
                                                </button>
                                            </h2>
                                            <div id="collapseGallery" class="accordion-collapse collapse" aria-labelledby="headingGallery" data-bs-parent="#galleryAccordion">
                                                <div class="accordion-body p-2">
                                                    <ul class="list-unstyled mb-0 content-hub-links">
                                                        <li><a href="{{ route('tenant.admin.image.gallery') }}" class="d-block py-1">{{ __('All Gallery') }}</a></li>
                                                        <li><a href="{{ route('tenant.admin.image.gallery.category') }}" class="d-block py-1">{{ __('Gallery Category') }}</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Brands (single card) --}}
                        <div class="col-md-4">
                            <a href="{{ route('tenant.admin.brands') }}" class="text-decoration-none text-reset">
                                <div class="card content-hub-card small-description h-100 border-primary">
                                    <div class="card-body">
                                        <span class="content-hub-tag manage-tag">{{ __('Manage') }}</span>
                                        <h5 class="card-title text-primary">
                                            <span class="content-hub-icon">
                                                <i class="mdi mdi-tag-multiple"></i>
                                            </span>
                                            <span>{{ __('Brands') }}</span>
                                        </h5>
                                        <p class="card-text small text-muted mb-0">{{ __('Manage brands list') }}</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        {{-- Advertisement (LIST) --}}
                        <div class="col-md-4">
                            <div class="card content-hub-card h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag list-tag">{{ __('List module') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-bullhorn-outline"></i>
                                        </span>
                                        <span>{{ __('Advertisement') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-2">{{ __('Manage advertisements and create new ones') }}</p>
                                    <div class="accordion content-hub-accordion" id="advertisementAccordion">
                                        <div class="accordion-item border-0">
                                            <h2 class="accordion-header" id="headingAdvertisement">
                                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdvertisement" aria-expanded="false" aria-controls="collapseAdvertisement">
                                                    {{ __('Open list') }}
                                                </button>
                                            </h2>
                                            <div id="collapseAdvertisement" class="accordion-collapse collapse" aria-labelledby="headingAdvertisement" data-bs-parent="#advertisementAccordion">
                                                <div class="accordion-body p-2">
                                                    <ul class="list-unstyled mb-0 content-hub-links">
                                                        <li><a href="{{ route('tenant.admin.advertisement') }}" class="d-block py-1">{{ __('All Advertisement') }}</a></li>
                                                        <li><a href="{{ route('tenant.admin.advertisement.new') }}" class="d-block py-1">{{ __('Add Advertisement') }}</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Portfolio (LIST) --}}
                        <div class="col-md-4">
                            <div class="card content-hub-card h-100">
                                <div class="card-body">
                                    <span class="content-hub-tag list-tag">{{ __('List module') }}</span>
                                    <h5 class="card-title">
                                        <span class="content-hub-icon">
                                            <i class="mdi mdi-briefcase-variant-outline"></i>
                                        </span>
                                        <span>{{ __('Portfolio') }}</span>
                                    </h5>
                                    <p class="card-text small text-muted mb-2">{{ __('Manage portfolio items and categories') }}</p>
                                    <div class="accordion content-hub-accordion" id="portfolioAccordion">
                                        <div class="accordion-item border-0">
                                            <h2 class="accordion-header" id="headingPortfolio">
                                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePortfolio" aria-expanded="false" aria-controls="collapsePortfolio">
                                                    {{ __('Open list') }}
                                                </button>
                                            </h2>
                                            <div id="collapsePortfolio" class="accordion-collapse collapse" aria-labelledby="headingPortfolio" data-bs-parent="#portfolioAccordion">
                                                <div class="accordion-body p-2">
                                                    <ul class="list-unstyled mb-0 content-hub-links">
                                                        <li><a href="{{ url('admin-home/portfolio') }}" class="d-block py-1">{{ __('All Portfolio') }}</a></li>
                                                        <li><a href="{{ url('admin-home/portfolio/new') }}" class="d-block py-1">{{ __('Add Portfolio') }}</a></li>
                                                        <li><a href="{{ url('admin-home/portfolio-category') }}" class="d-block py-1">{{ __('Portfolio Category') }}</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Badge Manage (LIST) --}}
                        <div class="col-md-4">
                            <div class="card content-hub-card small-description h-100">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div>
                                        <span class="content-hub-tag manage-tag">{{ __('Manage') }}</span>
                                        <h5 class="card-title">
                                            <span class="content-hub-icon">
                                                <i class="mdi mdi-ribbon"></i>
                                            </span>
                                            <span>{{ __('Badge Manage') }}</span>
                                        </h5>
                                        <p class="card-text small text-muted mb-2">{{ __('Manage badges list') }}</p>
                                    </div>
                                    <div>
                                        <a href="{{ url('admin-home/badge') }}" class="btn btn-outline-primary btn-sm">
                                            {{ __('Go to Badge list') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

