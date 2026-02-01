@php
    $effective_price = $room->sale_price && $room->sale_price > 0 && $room->sale_price < $room->base_cost 
        ? $room->sale_price 
        : $room->base_cost;
    $has_discount = $room->sale_price && $room->sale_price > 0 && $room->sale_price < $room->base_cost;
    $room_url = route('tenant.frontend.room_details', ['slug' => $room->slug ?? $room->id]);
@endphp

<div class="list-group-item room-list-item d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="room-info d-flex align-items-center gap-3">
        {{-- Thumbnail --}}
        @if($room->room_image && $room->room_image->count() > 0)
            <div class="room-thumb rounded overflow-hidden" style="width: 80px; height: 60px;">
                {!! render_image_markup_by_attachment_id($room->room_image->first()->image_id, '', 'img-fluid', '', 80, 60) !!}
            </div>
        @else
            <div class="room-thumb rounded overflow-hidden bg-light d-flex align-items-center justify-content-center" style="width: 80px; height: 60px;">
                <i class="las la-bed text-muted"></i>
            </div>
        @endif

        <div class="room-details">
            <h6 class="room-title mb-1">
                <a href="{{ $room_url }}" class="text-decoration-none">
                    {{ $room->getTranslation('name', $current_lang) }}
                </a>
                {{-- Badges --}}
                @if(!empty($room->is_featured) && $room->is_featured === 'on')
                    <span class="badge bg-warning text-dark ms-2">{{ __('Featured') }}</span>
                @endif
                @if(!empty($room->is_popular) && $room->is_popular === 'on')
                    <span class="badge bg-info ms-1">{{ __('Popular') }}</span>
                @endif
            </h6>
            
            @if(!empty($room->short_description))
                <p class="text-muted small mb-0">{{ \Illuminate\Support\Str::limit($room->getTranslation('short_description', $current_lang), 60) }}</p>
            @endif
            
            <div class="room-quick-meta d-flex gap-3 mt-1">
                @php
                    $max_guests = $room->max_guests ?? $room->room_types->max_guest ?? null;
                @endphp
                @if($max_guests && ($data['show_guests'] ?? true))
                    <small class="text-muted">
                        <i class="las la-user"></i> {{ $max_guests }} {{ __('guests') }}
                    </small>
                @endif
                @if($room->room_types && $room->room_types->bed_type)
                    <small class="text-muted">
                        <i class="las la-bed"></i> {{ $room->room_types->bed_type->name }}
                    </small>
                @endif
            </div>
        </div>
    </div>

    <div class="room-action d-flex align-items-center gap-3">
        @if($data['show_prices'] ?? true)
            <div class="price-wrapper text-end">
                @if($has_discount)
                    <span class="original-price text-decoration-line-through text-muted small d-block">
                        {{ amount_with_currency_symbol($room->base_cost) }}
                    </span>
                @endif
                <span class="current-price fw-bold text-primary">
                    {{ amount_with_currency_symbol($effective_price) }}
                </span>
                <small class="text-muted">/{{ __('night') }}</small>
            </div>
        @endif
        
        <a href="{{ $room_url }}" class="btn btn-sm btn-primary">
            {{ $data['book_now_text'] ?? __('Book Now') }}
        </a>
    </div>
</div>

<style>
/* Room List Item - Theme Compatible */
.room-list-item {
    background: var(--section-bg, #fff);
    border-color: var(--border-color, #e9ecef) !important;
    transition: all 0.3s ease;
    padding: 15px 20px;
}
.room-list-item:hover {
    background-color: rgba(var(--main-color-one-rgb, 0,0,0), 0.02);
}
.room-list-item .room-thumb img {
    object-fit: cover;
    width: 100%;
    height: 100%;
}
.room-list-item .room-title {
    font-family: var(--heading-font, inherit);
    font-size: 1rem;
    color: var(--heading-color, inherit);
}
.room-list-item .room-title a {
    color: inherit;
}
.room-list-item .room-title a:hover {
    color: var(--main-color-one, var(--bs-primary, inherit));
}
.room-list-item .current-price {
    font-size: 1.1rem;
    color: var(--main-color-one, var(--bs-primary, inherit));
}
.room-list-item .btn-primary {
    border-radius: var(--btn-radius, 5px);
    background-color: var(--main-color-one, var(--bs-primary));
    border-color: var(--main-color-one, var(--bs-primary));
}
</style>
