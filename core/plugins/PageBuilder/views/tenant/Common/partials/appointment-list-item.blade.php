@php
    $effective_price = $appointment->sale_price && $appointment->sale_price > 0 && $appointment->sale_price < $appointment->price 
        ? $appointment->sale_price 
        : $appointment->price;
    $has_discount = $appointment->sale_price && $appointment->sale_price > 0 && $appointment->sale_price < $appointment->price;
@endphp

<a href="{{ route('tenant.frontend.appointment.order.page', $appointment->slug) }}" class="list-group-item list-group-item-action appointment-list-item d-flex justify-content-between align-items-center py-3">
    <div class="service-info d-flex align-items-center flex-grow-1">
        {{-- Image (optional small thumbnail) --}}
        @if(($data['show_images'] ?? true) && $appointment->image)
            <div class="service-thumb me-3">
                {!! render_image_markup_by_attachment_id($appointment->image, '', 'rounded', '', 60, 60) !!}
            </div>
        @endif
        
        <div class="service-details">
            <h6 class="service-name mb-1">
                {{ $appointment->getTranslation('title', $current_lang) }}
                @if($appointment->is_popular === 'on')
                    <span class="badge bg-warning text-dark ms-2">{{ __('Popular') }}</span>
                @endif
                @if($appointment->is_featured === 'on')
                    <span class="badge bg-danger ms-1">{{ __('Featured') }}</span>
                @endif
            </h6>
            
            <div class="service-meta-list d-flex flex-wrap gap-3 text-muted small">
                {{-- Duration --}}
                @if(($data['show_duration'] ?? true) && $appointment->duration)
                    <span class="duration-info">
                        <i class="las la-clock me-1"></i>
                        @if($appointment->duration >= 60)
                            {{ floor($appointment->duration / 60) }}{{ __('h') }}
                            @if($appointment->duration % 60 > 0)
                                {{ $appointment->duration % 60 }}{{ __('min') }}
                            @endif
                        @else
                            {{ $appointment->duration }} {{ __('min') }}
                        @endif
                    </span>
                @endif
                
                {{-- Short description --}}
                @if($appointment->short_description)
                    <span class="desc-info d-none d-md-inline">
                        {{ \Illuminate\Support\Str::limit($appointment->getTranslation('short_description', $current_lang), 60) }}
                    </span>
                @endif
            </div>
        </div>
    </div>
    
    <div class="service-action d-flex align-items-center gap-3">
        {{-- Price --}}
        @if($data['show_prices'] ?? true)
            <div class="price-info text-end">
                @if($has_discount)
                    <span class="original-price text-decoration-line-through text-muted small d-block">
                        {{ amount_with_currency_symbol($appointment->price) }}
                    </span>
                @endif
                <span class="current-price fw-bold text-primary">
                    {{ amount_with_currency_symbol($effective_price) }}
                </span>
            </div>
        @endif
        
        <span class="book-arrow">
            <i class="las la-arrow-right"></i>
        </span>
    </div>
</a>

<style>
.appointment-list-item {
    border-left: 3px solid transparent;
    transition: all 0.3s ease;
}
.appointment-list-item:hover {
    border-left-color: var(--bs-primary, #2ECC71);
    background-color: #f8f9fa;
}
.appointment-list-item .service-thumb img {
    width: 60px;
    height: 60px;
    object-fit: cover;
}
.appointment-list-item .service-name {
    color: #2c3e50;
    font-weight: 600;
}
.appointment-list-item .book-arrow {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bs-primary, #2ECC71);
    color: white;
    border-radius: 50%;
    transition: transform 0.3s ease;
}
.appointment-list-item:hover .book-arrow {
    transform: translateX(5px);
}
</style>
