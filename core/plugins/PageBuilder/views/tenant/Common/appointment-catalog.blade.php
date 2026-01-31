@php
    $padding_top = $data['padding_top'] ?? '';
    $padding_bottom = $data['padding_bottom'] ?? '';
    $current_lang = $data['current_lang'] ?? app()->getLocale();
    $col_class = 'col-lg-' . (12 / (int)($data['columns'] ?? 3));
@endphp

<section class="appointment-catalog-area" 
    data-padding-top="{{ $padding_top }}" 
    data-padding-bottom="{{ $padding_bottom }}"
    @if(!empty($data['section_bg_image']))
        style="background-image: url({{ render_background_image_markup_by_attachment_id($data['section_bg_image']) }})"
    @endif
>
    <div class="container">
        {{-- Section Header --}}
        @if(!empty($data['section_title']) || !empty($data['section_subtitle']))
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center">
                    @if(!empty($data['section_title']))
                        <h2 class="section-title">{{ $data['section_title'] }}</h2>
                    @endif
                    @if(!empty($data['section_subtitle']))
                        <p class="section-subtitle text-muted mt-3">{{ $data['section_subtitle'] }}</p>
                    @endif
                </div>
            </div>
        @endif

        {{-- Display Mode: Hierarchical --}}
        @if($data['display_mode'] === 'hierarchical')
            @if($data['layout_style'] === 'accordion')
                {{-- Accordion Layout --}}
                <div class="appointment-accordion" id="appointmentAccordion">
                    @foreach($data['categories'] as $catIndex => $category)
                        <div class="accordion-item category-item mb-4">
                            <h2 class="accordion-header" id="heading{{ $category->id }}">
                                <button class="accordion-button {{ $catIndex > 0 ? 'collapsed' : '' }}" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse{{ $category->id }}" 
                                        aria-expanded="{{ $catIndex === 0 ? 'true' : 'false' }}">
                                    <div class="category-header d-flex align-items-center w-100">
                                        @if($data['show_category_icons'] && $category->icon)
                                            <span class="category-icon me-3">
                                                <i class="{{ $category->icon }}"></i>
                                            </span>
                                        @elseif($data['show_category_icons'] && $category->image)
                                            <span class="category-image me-3">
                                                {!! render_image_markup_by_attachment_id($category->image, '', 'img-fluid', '', 50, 50) !!}
                                            </span>
                                        @endif
                                        <span class="category-title fw-bold">{{ $category->getTranslation('title', $current_lang) }}</span>
                                        @if($category->description)
                                            <small class="category-desc ms-auto text-muted d-none d-md-block">
                                                {{ \Illuminate\Support\Str::limit($category->getTranslation('description', $current_lang), 50) }}
                                            </small>
                                        @endif
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse{{ $category->id }}" 
                                 class="accordion-collapse collapse {{ $catIndex === 0 ? 'show' : '' }}" 
                                 data-bs-parent="#appointmentAccordion">
                                <div class="accordion-body">
                                    {{-- Subcategories --}}
                                    @if($category->subcategories->count() > 0)
                                        @foreach($category->subcategories as $subIndex => $subcategory)
                                            <div class="subcategory-section mb-4">
                                                <h4 class="subcategory-title mb-3 pb-2 border-bottom">
                                                    @if($subcategory->icon)
                                                        <i class="{{ $subcategory->icon }} me-2"></i>
                                                    @endif
                                                    {{ $subcategory->getTranslation('title', $current_lang) }}
                                                </h4>
                                                
                                                @if(isset($subcategory->appointments_list) && $subcategory->appointments_list->count() > 0)
                                                    <div class="row g-4">
                                                        @foreach($subcategory->appointments_list as $appointment)
                                                            @include('pagebuilder::tenant.Common.partials.appointment-card', [
                                                                'appointment' => $appointment,
                                                                'data' => $data,
                                                                'col_class' => $col_class,
                                                                'current_lang' => $current_lang
                                                            ])
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p class="text-muted">{{ __('No services available in this subcategory') }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif

                                    {{-- Direct appointments (without subcategory) --}}
                                    @if(isset($category->direct_appointments) && $category->direct_appointments->count() > 0)
                                        <div class="direct-appointments-section">
                                            @if($category->subcategories->count() > 0)
                                                <h4 class="subcategory-title mb-3 pb-2 border-bottom">{{ __('Other Services') }}</h4>
                                            @endif
                                            <div class="row g-4">
                                                @foreach($category->direct_appointments as $appointment)
                                                    @include('pagebuilder::tenant.Common.partials.appointment-card', [
                                                        'appointment' => $appointment,
                                                        'data' => $data,
                                                        'col_class' => $col_class,
                                                        'current_lang' => $current_lang
                                                    ])
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            @elseif($data['layout_style'] === 'tabs')
                {{-- Tabs Layout --}}
                <div class="appointment-tabs">
                    <ul class="nav nav-pills nav-fill mb-4 flex-column flex-md-row" id="categoryTabs" role="tablist">
                        @foreach($data['categories'] as $catIndex => $category)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $catIndex === 0 ? 'active' : '' }}" 
                                        id="cat-tab-{{ $category->id }}" 
                                        data-bs-toggle="pill" 
                                        data-bs-target="#cat-content-{{ $category->id }}" 
                                        type="button" 
                                        role="tab">
                                    @if($data['show_category_icons'] && $category->icon)
                                        <i class="{{ $category->icon }} me-2"></i>
                                    @endif
                                    {{ $category->getTranslation('title', $current_lang) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                    
                    <div class="tab-content" id="categoryTabsContent">
                        @foreach($data['categories'] as $catIndex => $category)
                            <div class="tab-pane fade {{ $catIndex === 0 ? 'show active' : '' }}" 
                                 id="cat-content-{{ $category->id }}" 
                                 role="tabpanel">
                                
                                @if($category->description)
                                    <p class="category-description mb-4">{{ $category->getTranslation('description', $current_lang) }}</p>
                                @endif

                                {{-- Subcategories with nested tabs --}}
                                @if($category->subcategories->count() > 0)
                                    <ul class="nav nav-tabs mb-3" id="subCategoryTabs{{ $category->id }}" role="tablist">
                                        @foreach($category->subcategories as $subIndex => $subcategory)
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link {{ $subIndex === 0 ? 'active' : '' }}" 
                                                        data-bs-toggle="tab" 
                                                        data-bs-target="#sub-content-{{ $subcategory->id }}" 
                                                        type="button">
                                                    {{ $subcategory->getTranslation('title', $current_lang) }}
                                                </button>
                                            </li>
                                        @endforeach
                                        @if(isset($category->direct_appointments) && $category->direct_appointments->count() > 0)
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link {{ $category->subcategories->count() === 0 ? 'active' : '' }}" 
                                                        data-bs-toggle="tab" 
                                                        data-bs-target="#direct-{{ $category->id }}" 
                                                        type="button">
                                                    {{ __('Other Services') }}
                                                </button>
                                            </li>
                                        @endif
                                    </ul>
                                    
                                    <div class="tab-content">
                                        @foreach($category->subcategories as $subIndex => $subcategory)
                                            <div class="tab-pane fade {{ $subIndex === 0 ? 'show active' : '' }}" 
                                                 id="sub-content-{{ $subcategory->id }}">
                                                @if(isset($subcategory->appointments_list) && $subcategory->appointments_list->count() > 0)
                                                    <div class="row g-4">
                                                        @foreach($subcategory->appointments_list as $appointment)
                                                            @include('pagebuilder::tenant.Common.partials.appointment-card', [
                                                                'appointment' => $appointment,
                                                                'data' => $data,
                                                                'col_class' => $col_class,
                                                                'current_lang' => $current_lang
                                                            ])
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p class="text-muted">{{ __('No services available') }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                        
                                        @if(isset($category->direct_appointments) && $category->direct_appointments->count() > 0)
                                            <div class="tab-pane fade {{ $category->subcategories->count() === 0 ? 'show active' : '' }}" 
                                                 id="direct-{{ $category->id }}">
                                                <div class="row g-4">
                                                    @foreach($category->direct_appointments as $appointment)
                                                        @include('pagebuilder::tenant.Common.partials.appointment-card', [
                                                            'appointment' => $appointment,
                                                            'data' => $data,
                                                            'col_class' => $col_class,
                                                            'current_lang' => $current_lang
                                                        ])
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    {{-- Only direct appointments --}}
                                    @if(isset($category->direct_appointments) && $category->direct_appointments->count() > 0)
                                        <div class="row g-4">
                                            @foreach($category->direct_appointments as $appointment)
                                                @include('pagebuilder::tenant.Common.partials.appointment-card', [
                                                    'appointment' => $appointment,
                                                    'data' => $data,
                                                    'col_class' => $col_class,
                                                    'current_lang' => $current_lang
                                                ])
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

            @elseif($data['layout_style'] === 'cards')
                {{-- Cards Grid Layout --}}
                <div class="appointment-cards-grid">
                    @foreach($data['categories'] as $category)
                        <div class="category-card-section mb-5">
                            <div class="category-card-header d-flex align-items-center justify-content-between mb-4">
                                <h3 class="category-title mb-0">
                                    @if($data['show_category_icons'] && $category->icon)
                                        <i class="{{ $category->icon }} me-2" style="color: {{ $category->color ?? '#333' }}"></i>
                                    @endif
                                    {{ $category->getTranslation('title', $current_lang) }}
                                </h3>
                            </div>
                            
                            @if($category->description)
                                <p class="category-description mb-4">{{ $category->getTranslation('description', $current_lang) }}</p>
                            @endif

                            <div class="row g-4">
                                {{-- Subcategory appointments --}}
                                @foreach($category->subcategories as $subcategory)
                                    @if(isset($subcategory->appointments_list))
                                        @foreach($subcategory->appointments_list as $appointment)
                                            @include('pagebuilder::tenant.Common.partials.appointment-card', [
                                                'appointment' => $appointment,
                                                'data' => $data,
                                                'col_class' => $col_class,
                                                'current_lang' => $current_lang,
                                                'show_subcategory' => true,
                                                'subcategory' => $subcategory
                                            ])
                                        @endforeach
                                    @endif
                                @endforeach
                                
                                {{-- Direct appointments --}}
                                @if(isset($category->direct_appointments))
                                    @foreach($category->direct_appointments as $appointment)
                                        @include('pagebuilder::tenant.Common.partials.appointment-card', [
                                            'appointment' => $appointment,
                                            'data' => $data,
                                            'col_class' => $col_class,
                                            'current_lang' => $current_lang
                                        ])
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

            @elseif($data['layout_style'] === 'list')
                {{-- List View Layout --}}
                <div class="appointment-list-view">
                    @foreach($data['categories'] as $category)
                        <div class="category-list-section mb-5">
                            <h3 class="category-title border-bottom pb-3 mb-4">
                                @if($data['show_category_icons'] && $category->icon)
                                    <i class="{{ $category->icon }} me-2" style="color: {{ $category->color ?? '#333' }}"></i>
                                @endif
                                {{ $category->getTranslation('title', $current_lang) }}
                            </h3>

                            @foreach($category->subcategories as $subcategory)
                                @if(isset($subcategory->appointments_list) && $subcategory->appointments_list->count() > 0)
                                    <div class="subcategory-list mb-4">
                                        <h5 class="subcategory-title text-muted mb-3">
                                            {{ $subcategory->getTranslation('title', $current_lang) }}
                                        </h5>
                                        <div class="list-group">
                                            @foreach($subcategory->appointments_list as $appointment)
                                                @include('pagebuilder::tenant.Common.partials.appointment-list-item', [
                                                    'appointment' => $appointment,
                                                    'data' => $data,
                                                    'current_lang' => $current_lang
                                                ])
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            @if(isset($category->direct_appointments) && $category->direct_appointments->count() > 0)
                                <div class="direct-list">
                                    <div class="list-group">
                                        @foreach($category->direct_appointments as $appointment)
                                            @include('pagebuilder::tenant.Common.partials.appointment-list-item', [
                                                'appointment' => $appointment,
                                                'data' => $data,
                                                'current_lang' => $current_lang
                                            ])
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

        {{-- Display Mode: Categories Only --}}
        @elseif($data['display_mode'] === 'categories_only')
            <div class="row g-4">
                @foreach($data['categories'] as $category)
                    <div class="{{ $col_class }}">
                        <div class="category-showcase-card h-100">
                            @if($category->image)
                                <div class="category-image-wrapper">
                                    {!! render_image_markup_by_attachment_id($category->image, '', 'img-fluid w-100') !!}
                                </div>
                            @endif
                            <div class="category-content p-4">
                                <h4 class="category-title">
                                    @if($data['show_category_icons'] && $category->icon)
                                        <i class="{{ $category->icon }} me-2" style="color: {{ $category->color ?? '#333' }}"></i>
                                    @endif
                                    {{ $category->getTranslation('title', $current_lang) }}
                                </h4>
                                @if($category->description)
                                    <p class="category-desc text-muted">{{ \Illuminate\Support\Str::limit($category->getTranslation('description', $current_lang), 100) }}</p>
                                @endif
                                <a href="{{ route('tenant.frontend.appointment.category', $category->slug ?? $category->id) }}" class="btn btn-outline-primary mt-3">
                                    {{ $data['view_all_text'] }} <i class="las la-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        {{-- Display Mode: Services Grid (Flat) --}}
        @elseif($data['display_mode'] === 'services_grid')
            <div class="row g-4">
                @foreach($data['categories'] as $category)
                    @foreach($category->subcategories as $subcategory)
                        @if(isset($subcategory->appointments_list))
                            @foreach($subcategory->appointments_list as $appointment)
                                @include('pagebuilder::tenant.Common.partials.appointment-card', [
                                    'appointment' => $appointment,
                                    'data' => $data,
                                    'col_class' => $col_class,
                                    'current_lang' => $current_lang
                                ])
                            @endforeach
                        @endif
                    @endforeach
                    @if(isset($category->direct_appointments))
                        @foreach($category->direct_appointments as $appointment)
                            @include('pagebuilder::tenant.Common.partials.appointment-card', [
                                'appointment' => $appointment,
                                'data' => $data,
                                'col_class' => $col_class,
                                'current_lang' => $current_lang
                            ])
                        @endforeach
                    @endif
                @endforeach
            </div>

        {{-- Display Mode: Featured Services Only --}}
        @elseif($data['display_mode'] === 'featured_services')
            <div class="row g-4">
                @forelse($data['featured_services'] as $appointment)
                    @include('pagebuilder::tenant.Common.partials.appointment-card', [
                        'appointment' => $appointment,
                        'data' => $data,
                        'col_class' => $col_class,
                        'current_lang' => $current_lang,
                        'show_featured_badge' => true
                    ])
                @empty
                    <div class="col-12 text-center">
                        <p class="text-muted">{{ __('No featured services available') }}</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</section>

<style>
/* Appointment Catalog - Theme Compatible Styles */
.appointment-catalog-area {
    background-size: cover;
    background-position: center;
}
.appointment-catalog-area .section-title {
    font-family: inherit; /* Inherit from theme */
    font-weight: 700;
}
.appointment-catalog-area .accordion-button:not(.collapsed) {
    background-color: var(--main-color-one, var(--bs-primary, currentColor));
    color: #fff;
}
.appointment-catalog-area .category-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--main-color-one-rgb, rgba(0,0,0,0.05));
    border-radius: 50%;
    font-size: 1.5rem;
    color: var(--main-color-one, var(--bs-primary, inherit));
}
.appointment-catalog-area .nav-pills .nav-link {
    border-radius: var(--btn-radius, 5px);
    padding: 12px 25px;
    margin: 5px;
    transition: all 0.3s ease;
    font-family: inherit;
}
.appointment-catalog-area .nav-pills .nav-link.active {
    background: var(--main-color-one, var(--bs-primary, currentColor));
}
.appointment-catalog-area .category-showcase-card {
    background: var(--section-bg, #fff);
    border-radius: var(--card-radius, 10px);
    overflow: hidden;
    box-shadow: var(--card-shadow, 0 2px 10px rgba(0,0,0,0.08));
    transition: transform 0.3s ease;
}
.appointment-catalog-area .category-showcase-card:hover {
    transform: translateY(-5px);
}
.appointment-catalog-area .category-image-wrapper {
    height: 200px;
    overflow: hidden;
}
.appointment-catalog-area .category-image-wrapper img {
    object-fit: cover;
    height: 100%;
}
.appointment-catalog-area h2, 
.appointment-catalog-area h3, 
.appointment-catalog-area h4, 
.appointment-catalog-area h5 {
    font-family: var(--heading-font, inherit);
    color: var(--heading-color, inherit);
}
.appointment-catalog-area p,
.appointment-catalog-area span,
.appointment-catalog-area a {
    font-family: var(--body-font, inherit);
}
.appointment-catalog-area .btn-primary,
.appointment-catalog-area .btn-outline-primary {
    background-color: var(--main-color-one, var(--bs-primary));
    border-color: var(--main-color-one, var(--bs-primary));
    border-radius: var(--btn-radius, 5px);
}
.appointment-catalog-area .btn-outline-primary {
    background-color: transparent;
    color: var(--main-color-one, var(--bs-primary));
}
.appointment-catalog-area .btn-outline-primary:hover {
    background-color: var(--main-color-one, var(--bs-primary));
    color: #fff;
}
</style>
