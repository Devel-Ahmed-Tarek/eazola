@extends('tenant.admin.admin-master')
@section('title') {{ __('Content Management') }} @endsection

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">{{ __('Content Management') }}</h4>
                    <div class="row g-3">
                        {{-- Blogs (LIST) --}}
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">{{ __('Blogs') }}</h5>
                                    <p class="card-text small text-muted mb-2">{{ __('Manage all blog content') }}</p>
                                    <div class="accordion" id="blogsAccordion">
                                        <div class="accordion-item border-0">
                                            <h2 class="accordion-header" id="headingBlogs">
                                                <button class="accordion-button p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBlogs" aria-expanded="true" aria-controls="collapseBlogs">
                                                    {{ __('Open list') }}
                                                </button>
                                            </h2>
                                            <div id="collapseBlogs" class="accordion-collapse collapse show" aria-labelledby="headingBlogs" data-bs-parent="#blogsAccordion">
                                                <div class="accordion-body p-2">
                                                    <ul class="list-unstyled mb-0">
                                                        <li><a href="{{ route('tenant.admin.blog') }}" class="d-block py-1">{{ __('All Blogs') }}</a></li>
                                                        <li><a href="{{ route('tenant.admin.blog.new') }}" class="d-block py-1">{{ __('Add New Blog') }}</a></li>
                                                        <li><a href="{{ route('tenant.admin.blog.category') }}" class="d-block py-1">{{ __('Blog Category') }}</a></li>
                                                        <li><a href="{{ route('tenant.admin.blog.settings') }}" class="d-block py-1">{{ __('Blog Settings') }}</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Services (LIST) --}}
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">{{ __('Services') }}</h5>
                                    <p class="card-text small text-muted mb-2">{{ __('Manage all services') }}</p>
                                    <div class="accordion" id="servicesAccordion">
                                        <div class="accordion-item border-0">
                                            <h2 class="accordion-header" id="headingServices">
                                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseServices" aria-expanded="false" aria-controls="collapseServices">
                                                    {{ __('Open list') }}
                                                </button>
                                            </h2>
                                            <div id="collapseServices" class="accordion-collapse collapse" aria-labelledby="headingServices" data-bs-parent="#servicesAccordion">
                                                <div class="accordion-body p-2">
                                                    <ul class="list-unstyled mb-0">
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
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">{{ __('Knowledgebase') }}</h5>
                                    <p class="card-text small text-muted mb-2">{{ __('Manage articles and categories') }}</p>
                                    <div class="accordion" id="kbAccordion">
                                        <div class="accordion-item border-0">
                                            <h2 class="accordion-header" id="headingKb">
                                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKb" aria-expanded="false" aria-controls="collapseKb">
                                                    {{ __('Open list') }}
                                                </button>
                                            </h2>
                                            <div id="collapseKb" class="accordion-collapse collapse" aria-labelledby="headingKb" data-bs-parent="#kbAccordion">
                                                <div class="accordion-body p-2">
                                                    <ul class="list-unstyled mb-0">
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
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">{{ __('FAQs') }}</h5>
                                    <p class="card-text small text-muted mb-2">{{ __('Frequently asked questions') }}</p>
                                    <div class="accordion" id="faqAccordion">
                                        <div class="accordion-item border-0">
                                            <h2 class="accordion-header" id="headingFaq">
                                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFaq" aria-expanded="false" aria-controls="collapseFaq">
                                                    {{ __('Open list') }}
                                                </button>
                                            </h2>
                                            <div id="collapseFaq" class="accordion-collapse collapse" aria-labelledby="headingFaq" data-bs-parent="#faqAccordion">
                                                <div class="accordion-body p-2">
                                                    <ul class="list-unstyled mb-0">
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
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ __('Testimonials') }}</h5>
                                        <p class="card-text small text-muted mb-0">{{ __('Manage client testimonials') }}</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        {{-- Image Gallery (LIST) --}}
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">{{ __('Image Gallery') }}</h5>
                                    <p class="card-text small text-muted mb-2">{{ __('Manage gallery images and categories') }}</p>
                                    <div class="accordion" id="galleryAccordion">
                                        <div class="accordion-item border-0">
                                            <h2 class="accordion-header" id="headingGallery">
                                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGallery" aria-expanded="false" aria-controls="collapseGallery">
                                                    {{ __('Open list') }}
                                                </button>
                                            </h2>
                                            <div id="collapseGallery" class="accordion-collapse collapse" aria-labelledby="headingGallery" data-bs-parent="#galleryAccordion">
                                                <div class="accordion-body p-2">
                                                    <ul class="list-unstyled mb-0">
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
                                <div class="card h-100 border-primary">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary">{{ __('Brands') }}</h5>
                                        <p class="card-text small text-muted mb-0">{{ __('Manage brands list') }}</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        {{-- Advertisement (LIST) --}}
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">{{ __('Advertisement') }}</h5>
                                    <p class="card-text small text-muted mb-2">{{ __('Manage advertisements and create new ones') }}</p>
                                    <div class="accordion" id="advertisementAccordion">
                                        <div class="accordion-item border-0">
                                            <h2 class="accordion-header" id="headingAdvertisement">
                                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdvertisement" aria-expanded="false" aria-controls="collapseAdvertisement">
                                                    {{ __('Open list') }}
                                                </button>
                                            </h2>
                                            <div id="collapseAdvertisement" class="accordion-collapse collapse" aria-labelledby="headingAdvertisement" data-bs-parent="#advertisementAccordion">
                                                <div class="accordion-body p-2">
                                                    <ul class="list-unstyled mb-0">
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
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">{{ __('Portfolio') }}</h5>
                                    <p class="card-text small text-muted mb-2">{{ __('Manage portfolio items and categories') }}</p>
                                    <div class="accordion" id="portfolioAccordion">
                                        <div class="accordion-item border-0">
                                            <h2 class="accordion-header" id="headingPortfolio">
                                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePortfolio" aria-expanded="false" aria-controls="collapsePortfolio">
                                                    {{ __('Open list') }}
                                                </button>
                                            </h2>
                                            <div id="collapsePortfolio" class="accordion-collapse collapse" aria-labelledby="headingPortfolio" data-bs-parent="#portfolioAccordion">
                                                <div class="accordion-body p-2">
                                                    <ul class="list-unstyled mb-0">
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
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">{{ __('Badge Manage') }}</h5>
                                    <p class="card-text small text-muted mb-2">{{ __('Manage badges list') }}</p>
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
@endsection

