@php
    $padding_top = $data['padding_top'] ?? '';
    $padding_bottom = $data['padding_bottom'] ?? '';
    $current_lang = $data['current_lang'] ?? app()->getLocale();
    $col_class = 'col-lg-' . (12 / (int)($data['columns'] ?? 3));
@endphp

<section class="hotel-catalog-area" 
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
                <div class="hotel-accordion" id="hotelAccordion">
                    @foreach($data['hotels'] as $hotelIndex => $hotel)
                        <div class="accordion-item hotel-item mb-4">
                            <h2 class="accordion-header" id="heading{{ $hotel->id }}">
                                <button class="accordion-button {{ $hotelIndex > 0 ? 'collapsed' : '' }}" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse{{ $hotel->id }}" 
                                        aria-expanded="{{ $hotelIndex === 0 ? 'true' : 'false' }}">
                                    <div class="hotel-header d-flex align-items-center w-100">
                                        @if($data['show_hotel_icons'] && !empty($hotel->icon))
                                            <span class="hotel-icon me-3">
                                                <i class="{{ $hotel->icon }}"></i>
                                            </span>
                                        @elseif($data['show_hotel_icons'] && !empty($hotel->image))
                                            <span class="hotel-image me-3">
                                                {!! render_image_markup_by_attachment_id($hotel->image, '', 'img-fluid', '', 50, 50) !!}
                                            </span>
                                        @endif
                                        <span class="hotel-title fw-bold">{{ $hotel->getTranslation('name', $current_lang) }}</span>
                                        @if($hotel->location)
                                            <small class="hotel-location ms-auto text-muted d-none d-md-block">
                                                <i class="las la-map-marker"></i> {{ \Illuminate\Support\Str::limit($hotel->getTranslation('location', $current_lang), 50) }}
                                            </small>
                                        @endif
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse{{ $hotel->id }}" 
                                 class="accordion-collapse collapse {{ $hotelIndex === 0 ? 'show' : '' }}" 
                                 data-bs-parent="#hotelAccordion">
                                <div class="accordion-body">
                                    {{-- Room Types --}}
                                    @if($hotel->room_type->count() > 0)
                                        @foreach($hotel->room_type as $roomTypeIndex => $roomType)
                                            <div class="roomtype-section mb-4">
                                                <h4 class="roomtype-title mb-3 pb-2 border-bottom">
                                                    @if(!empty($roomType->icon))
                                                        <i class="{{ $roomType->icon }} me-2"></i>
                                                    @endif
                                                    {{ $roomType->getTranslation('name', $current_lang) }}
                                                    @if($data['show_guests'] && $roomType->max_guest)
                                                        <small class="text-muted ms-2">
                                                            <i class="las la-user"></i> {{ __('Max') }}: {{ $roomType->max_guest }} {{ __('Guests') }}
                                                        </small>
                                                    @endif
                                                </h4>
                                                
                                                @if(isset($roomType->rooms_list) && $roomType->rooms_list->count() > 0)
                                                    <div class="row g-4">
                                                        @foreach($roomType->rooms_list as $room)
                                                            @include('pagebuilder::tenant.Common.partials.room-card', [
                                                                'room' => $room,
                                                                'data' => $data,
                                                                'col_class' => $col_class,
                                                                'current_lang' => $current_lang
                                                            ])
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p class="text-muted">{{ __('No rooms available in this type') }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            @elseif($data['layout_style'] === 'tabs')
                {{-- Tabs Layout --}}
                <div class="hotel-tabs">
                    <ul class="nav nav-pills nav-fill mb-4 flex-column flex-md-row" id="hotelTabs" role="tablist">
                        @foreach($data['hotels'] as $hotelIndex => $hotel)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $hotelIndex === 0 ? 'active' : '' }}" 
                                        id="hotel-tab-{{ $hotel->id }}" 
                                        data-bs-toggle="pill" 
                                        data-bs-target="#hotel-content-{{ $hotel->id }}" 
                                        type="button" 
                                        role="tab">
                                    @if($data['show_hotel_icons'] && !empty($hotel->icon))
                                        <i class="{{ $hotel->icon }} me-2"></i>
                                    @endif
                                    {{ $hotel->getTranslation('name', $current_lang) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                    
                    <div class="tab-content" id="hotelTabsContent">
                        @foreach($data['hotels'] as $hotelIndex => $hotel)
                            <div class="tab-pane fade {{ $hotelIndex === 0 ? 'show active' : '' }}" 
                                 id="hotel-content-{{ $hotel->id }}" 
                                 role="tabpanel">
                                
                                @if($hotel->location)
                                    <p class="hotel-location mb-2"><i class="las la-map-marker"></i> {{ $hotel->getTranslation('location', $current_lang) }}</p>
                                @endif
                                @if($hotel->about)
                                    <p class="hotel-description mb-4">{{ $hotel->getTranslation('about', $current_lang) }}</p>
                                @endif

                                {{-- Room Types with nested tabs --}}
                                @if($hotel->room_type->count() > 0)
                                    <ul class="nav nav-tabs mb-3" id="roomTypeTabs{{ $hotel->id }}" role="tablist">
                                        @foreach($hotel->room_type as $roomTypeIndex => $roomType)
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link {{ $roomTypeIndex === 0 ? 'active' : '' }}" 
                                                        data-bs-toggle="tab" 
                                                        data-bs-target="#roomtype-content-{{ $roomType->id }}" 
                                                        type="button">
                                                    {{ $roomType->getTranslation('name', $current_lang) }}
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                    
                                    <div class="tab-content">
                                        @foreach($hotel->room_type as $roomTypeIndex => $roomType)
                                            <div class="tab-pane fade {{ $roomTypeIndex === 0 ? 'show active' : '' }}" 
                                                 id="roomtype-content-{{ $roomType->id }}">
                                                @if(isset($roomType->rooms_list) && $roomType->rooms_list->count() > 0)
                                                    <div class="row g-4">
                                                        @foreach($roomType->rooms_list as $room)
                                                            @include('pagebuilder::tenant.Common.partials.room-card', [
                                                                'room' => $room,
                                                                'data' => $data,
                                                                'col_class' => $col_class,
                                                                'current_lang' => $current_lang
                                                            ])
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p class="text-muted">{{ __('No rooms available') }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

            @elseif($data['layout_style'] === 'cards')
                {{-- Cards Grid Layout --}}
                <div class="hotel-cards-grid">
                    @foreach($data['hotels'] as $hotel)
                        <div class="hotel-card-section mb-5">
                            <div class="hotel-card-header d-flex align-items-center justify-content-between mb-4">
                                <h3 class="hotel-title mb-0">
                                    @if($data['show_hotel_icons'] && !empty($hotel->icon))
                                        <i class="{{ $hotel->icon }} me-2"></i>
                                    @endif
                                    {{ $hotel->getTranslation('name', $current_lang) }}
                                </h3>
                                @if($hotel->location)
                                    <small class="text-muted"><i class="las la-map-marker"></i> {{ $hotel->getTranslation('location', $current_lang) }}</small>
                                @endif
                            </div>
                            
                            @if($hotel->about)
                                <p class="hotel-description mb-4">{{ \Illuminate\Support\Str::limit($hotel->getTranslation('about', $current_lang), 150) }}</p>
                            @endif

                            <div class="row g-4">
                                @foreach($hotel->room_type as $roomType)
                                    @if(isset($roomType->rooms_list))
                                        @foreach($roomType->rooms_list as $room)
                                            @include('pagebuilder::tenant.Common.partials.room-card', [
                                                'room' => $room,
                                                'data' => $data,
                                                'col_class' => $col_class,
                                                'current_lang' => $current_lang,
                                                'show_roomtype' => true,
                                                'roomType' => $roomType
                                            ])
                                        @endforeach
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

            @elseif($data['layout_style'] === 'list')
                {{-- List View Layout --}}
                <div class="hotel-list-view">
                    @foreach($data['hotels'] as $hotel)
                        <div class="hotel-list-section mb-5">
                            <h3 class="hotel-title border-bottom pb-3 mb-4">
                                @if($data['show_hotel_icons'] && !empty($hotel->icon))
                                    <i class="{{ $hotel->icon }} me-2"></i>
                                @endif
                                {{ $hotel->getTranslation('name', $current_lang) }}
                                @if($hotel->location)
                                    <small class="text-muted ms-2"><i class="las la-map-marker"></i> {{ $hotel->getTranslation('location', $current_lang) }}</small>
                                @endif
                            </h3>

                            @foreach($hotel->room_type as $roomType)
                                @if(isset($roomType->rooms_list) && $roomType->rooms_list->count() > 0)
                                    <div class="roomtype-list mb-4">
                                        <h5 class="roomtype-title text-muted mb-3">
                                            {{ $roomType->getTranslation('name', $current_lang) }}
                                        </h5>
                                        <div class="list-group">
                                            @foreach($roomType->rooms_list as $room)
                                                @include('pagebuilder::tenant.Common.partials.room-list-item', [
                                                    'room' => $room,
                                                    'data' => $data,
                                                    'current_lang' => $current_lang
                                                ])
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif

        {{-- Display Mode: Hotels Only --}}
        @elseif($data['display_mode'] === 'hotels_only')
            <div class="row g-4">
                @foreach($data['hotels'] as $hotel)
                    <div class="{{ $col_class }}">
                        <div class="hotel-showcase-card h-100">
                            @if(!empty($hotel->image))
                                <div class="hotel-image-wrapper">
                                    {!! render_image_markup_by_attachment_id($hotel->image, '', 'img-fluid w-100') !!}
                                </div>
                            @elseif($hotel->hotel_images->count() > 0)
                                <div class="hotel-image-wrapper">
                                    {!! render_image_markup_by_attachment_id($hotel->hotel_images->first()->image_id, '', 'img-fluid w-100') !!}
                                </div>
                            @endif
                            <div class="hotel-content p-4">
                                <h4 class="hotel-title">
                                    @if($data['show_hotel_icons'] && !empty($hotel->icon))
                                        <i class="{{ $hotel->icon }} me-2"></i>
                                    @endif
                                    {{ $hotel->getTranslation('name', $current_lang) }}
                                </h4>
                                @if($hotel->location)
                                    <p class="hotel-location text-muted mb-2"><i class="las la-map-marker"></i> {{ $hotel->getTranslation('location', $current_lang) }}</p>
                                @endif
                                @if($hotel->about)
                                    <p class="hotel-desc text-muted">{{ \Illuminate\Support\Str::limit($hotel->getTranslation('about', $current_lang), 100) }}</p>
                                @endif
                                <a href="{{ route('tenant.frontend.hotel-details', ['slug' => $hotel->slug ?? $hotel->id]) }}" class="btn btn-outline-primary mt-3">
                                    {{ $data['view_all_text'] }} <i class="las la-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        {{-- Display Mode: Rooms Grid (Flat) --}}
        @elseif($data['display_mode'] === 'rooms_grid')
            <div class="row g-4">
                @foreach($data['hotels'] as $hotel)
                    @foreach($hotel->room_type as $roomType)
                        @if(isset($roomType->rooms_list))
                            @foreach($roomType->rooms_list as $room)
                                @include('pagebuilder::tenant.Common.partials.room-card', [
                                    'room' => $room,
                                    'data' => $data,
                                    'col_class' => $col_class,
                                    'current_lang' => $current_lang
                                ])
                            @endforeach
                        @endif
                    @endforeach
                @endforeach
            </div>

        {{-- Display Mode: Featured Rooms Only --}}
        @elseif($data['display_mode'] === 'featured_rooms')
            <div class="row g-4">
                @forelse($data['featured_rooms'] as $room)
                    @include('pagebuilder::tenant.Common.partials.room-card', [
                        'room' => $room,
                        'data' => $data,
                        'col_class' => $col_class,
                        'current_lang' => $current_lang,
                        'show_featured_badge' => true
                    ])
                @empty
                    <div class="col-12 text-center">
                        <p class="text-muted">{{ __('No featured rooms available') }}</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</section>

<style>
/* Hotel Catalog - Theme Compatible Styles */
.hotel-catalog-area {
    background-size: cover;
    background-position: center;
}
.hotel-catalog-area .section-title {
    font-family: var(--heading-font, inherit);
    font-weight: 700;
    color: var(--heading-color, inherit);
}
.hotel-catalog-area .accordion-button:not(.collapsed) {
    background-color: var(--main-color-one, var(--bs-primary, currentColor));
    color: #fff;
}
.hotel-catalog-area .hotel-icon {
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
.hotel-catalog-area .nav-pills .nav-link {
    border-radius: var(--btn-radius, 5px);
    padding: 12px 25px;
    margin: 5px;
    transition: all 0.3s ease;
    font-family: var(--body-font, inherit);
}
.hotel-catalog-area .nav-pills .nav-link.active {
    background: var(--main-color-one, var(--bs-primary, currentColor));
}
.hotel-catalog-area .hotel-showcase-card {
    background: var(--section-bg, #fff);
    border-radius: var(--card-radius, 10px);
    overflow: hidden;
    box-shadow: var(--card-shadow, 0 2px 10px rgba(0,0,0,0.08));
    transition: transform 0.3s ease;
}
.hotel-catalog-area .hotel-showcase-card:hover {
    transform: translateY(-5px);
}
.hotel-catalog-area .hotel-image-wrapper {
    height: 200px;
    overflow: hidden;
}
.hotel-catalog-area .hotel-image-wrapper img {
    object-fit: cover;
    height: 100%;
}
.hotel-catalog-area h2, 
.hotel-catalog-area h3, 
.hotel-catalog-area h4, 
.hotel-catalog-area h5 {
    font-family: var(--heading-font, inherit);
    color: var(--heading-color, inherit);
}
.hotel-catalog-area p,
.hotel-catalog-area span,
.hotel-catalog-area a {
    font-family: var(--body-font, inherit);
}
.hotel-catalog-area .btn-primary,
.hotel-catalog-area .btn-outline-primary {
    background-color: var(--main-color-one, var(--bs-primary));
    border-color: var(--main-color-one, var(--bs-primary));
    border-radius: var(--btn-radius, 5px);
}
.hotel-catalog-area .btn-outline-primary {
    background-color: transparent;
    color: var(--main-color-one, var(--bs-primary));
}
.hotel-catalog-area .btn-outline-primary:hover {
    background-color: var(--main-color-one, var(--bs-primary));
    color: #fff;
}
</style>
