@php
    $effective_price = $room->sale_price && $room->sale_price > 0 && $room->sale_price < $room->base_cost 
        ? $room->sale_price 
        : $room->base_cost;
    $has_discount = $room->sale_price && $room->sale_price > 0 && $room->sale_price < $room->base_cost;
@endphp

<div class="{{ $col_class ?? 'col-lg-4 col-md-6' }}">
    <div class="room-service-card h-100">
        {{-- Featured Badge --}}
        @if(!empty($show_featured_badge) && !empty($room->is_featured) && $room->is_featured === 'on')
            <span class="featured-badge">{{ __('Featured') }}</span>
        @endif
        
        {{-- Discount Badge --}}
        @if($has_discount)
            @php
                $discount_percent = round((($room->base_cost - $room->sale_price) / $room->base_cost) * 100);
            @endphp
            <span class="discount-badge">-{{ $discount_percent }}%</span>
        @endif

        {{-- Room Image --}}
        @if(($data['show_images'] ?? true))
            <div class="room-image-wrapper">
                @php
                    $room_url = route('tenant.frontend.room_details', ['slug' => $room->slug ?? $room->id]);
                @endphp
                @if($room->room_image && $room->room_image->count() > 0)
                    <a href="{{ $room_url }}">
                        {!! render_image_markup_by_attachment_id($room->room_image->first()->image_id, '', 'img-fluid w-100') !!}
                    </a>
                @else
                    <a href="{{ $room_url }}">
                        <div class="no-image-placeholder d-flex align-items-center justify-content-center bg-light h-100">
                            <i class="las la-bed text-muted" style="font-size: 4rem;"></i>
                        </div>
                    </a>
                @endif
            </div>
        @endif

        <div class="room-content p-4">
            {{-- Room Type Label --}}
            @if(!empty($show_roomtype) && isset($roomType))
                <span class="roomtype-label text-muted small mb-2 d-block">
                    {{ $roomType->getTranslation('name', $current_lang) }}
                </span>
            @endif

            {{-- Title --}}
            <h5 class="room-title mb-2">
                <a href="{{ $room_url }}">
                    {{ $room->getTranslation('name', $current_lang) }}
                </a>
            </h5>

            {{-- Short Description --}}
            @if(!empty($room->short_description))
                <p class="room-description text-muted small mb-3">
                    {{ \Illuminate\Support\Str::limit($room->getTranslation('short_description', $current_lang), 80) }}
                </p>
            @elseif($room->description)
                <p class="room-description text-muted small mb-3">
                    {{ \Illuminate\Support\Str::limit(strip_tags($room->getTranslation('description', $current_lang)), 80) }}
                </p>
            @endif

            {{-- Meta Info --}}
            <div class="room-meta d-flex flex-wrap gap-3 mb-3">
                {{-- Max Guests --}}
                @if(($data['show_guests'] ?? true))
                    @php
                        $max_guests = $room->max_guests ?? $room->room_types->max_guest ?? null;
                    @endphp
                    @if($max_guests)
                        <span class="meta-item guests">
                            <i class="las la-user"></i> {{ $max_guests }} {{ __('Guests') }}
                        </span>
                    @endif
                @endif

                {{-- Popular Badge --}}
                @if(!empty($room->is_popular) && $room->is_popular === 'on')
                    <span class="meta-item popular">
                        <i class="las la-star"></i> {{ __('Popular') }}
                    </span>
                @endif

                {{-- Bed Type --}}
                @if($room->room_types && $room->room_types->bed_type)
                    <span class="meta-item bed-type">
                        <i class="las la-bed"></i> {{ $room->room_types->bed_type->name }}
                    </span>
                @endif
            </div>

            {{-- Amenities --}}
            @if(($data['show_amenities'] ?? true) && $room->room_types && $room->room_types->room_type_amenities->count() > 0)
                <div class="room-amenities mb-3">
                    @foreach($room->room_types->room_type_amenities->take(4) as $amenity)
                        <span class="amenity-tag badge bg-light text-dark me-1 mb-1">
                            @if($amenity->icon)
                                <i class="{{ $amenity->icon }}"></i>
                            @endif
                            {{ $amenity->name }}
                        </span>
                    @endforeach
                    @if($room->room_types->room_type_amenities->count() > 4)
                        <span class="amenity-tag badge bg-light text-dark">+{{ $room->room_types->room_type_amenities->count() - 4 }}</span>
                    @endif
                </div>
            @endif

            {{-- Price & Book Button --}}
            <div class="room-footer d-flex justify-content-between align-items-center mt-auto">
                @if($data['show_prices'] ?? true)
                    <div class="price-wrapper">
                        @if($has_discount)
                            <span class="original-price text-decoration-line-through text-muted small">
                                {{ amount_with_currency_symbol($room->base_cost) }}
                            </span>
                        @endif
                        <span class="current-price fw-bold text-primary">
                            {{ amount_with_currency_symbol($effective_price) }}
                        </span>
                        <small class="text-muted">/ {{ __('night') }}</small>
                    </div>
                @endif
                
                <a href="{{ $room_url }}" class="btn btn-sm btn-primary book-btn">
                    {{ $data['book_now_text'] ?? __('Book Now') }}
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Room Card - Theme Compatible */
.room-service-card {
    background: var(--section-bg, #fff);
    border-radius: var(--card-radius, 10px);
    overflow: hidden;
    box-shadow: var(--card-shadow, 0 2px 10px rgba(0,0,0,0.08));
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    flex-direction: column;
}
.room-service-card:hover {
    transform: translateY(-5px);
}
.room-service-card .featured-badge,
.room-service-card .discount-badge {
    position: absolute;
    top: 15px;
    padding: 5px 12px;
    border-radius: var(--btn-radius, 5px);
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
}
.room-service-card .featured-badge {
    left: 15px;
    background: var(--secondary-color, #f39c12);
    color: #fff;
}
.room-service-card .discount-badge {
    right: 15px;
    background: var(--danger-color, #e74c3c);
    color: #fff;
}
.room-service-card .room-image-wrapper {
    height: 200px;
    overflow: hidden;
}
.room-service-card .room-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}
.room-service-card .no-image-placeholder {
    height: 200px;
}
.room-service-card:hover .room-image-wrapper img {
    transform: scale(1.05);
}
.room-service-card .room-content {
    flex: 1;
    display: flex;
    flex-direction: column;
}
.room-service-card .room-title {
    font-family: var(--heading-font, inherit);
}
.room-service-card .room-title a {
    color: var(--heading-color, inherit);
    text-decoration: none;
    transition: color 0.3s ease;
}
.room-service-card .room-title a:hover {
    color: var(--main-color-one, var(--bs-primary, inherit));
}
.room-service-card .room-description {
    font-family: var(--body-font, inherit);
}
.room-service-card .room-meta .meta-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.85rem;
    color: var(--paragraph-color, #666);
}
.room-service-card .room-meta .popular {
    color: var(--secondary-color, #f39c12);
}
.room-service-card .current-price {
    font-size: 1.25rem;
    color: var(--main-color-one, var(--bs-primary, inherit));
}
.room-service-card .book-btn {
    border-radius: var(--btn-radius, 5px);
    padding: 8px 20px;
    background-color: var(--main-color-one, var(--bs-primary));
    border-color: var(--main-color-one, var(--bs-primary));
}
.room-service-card .book-btn:hover {
    opacity: 0.9;
}
.room-service-card .amenity-tag {
    font-size: 0.75rem;
}
</style>
