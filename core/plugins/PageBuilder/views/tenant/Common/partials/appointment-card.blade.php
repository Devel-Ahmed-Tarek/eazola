@php
    $effective_price = $appointment->sale_price && $appointment->sale_price > 0 && $appointment->sale_price < $appointment->price 
        ? $appointment->sale_price 
        : $appointment->price;
    $has_discount = $appointment->sale_price && $appointment->sale_price > 0 && $appointment->sale_price < $appointment->price;
@endphp

<div class="{{ $col_class ?? 'col-lg-4 col-md-6' }}">
    <div class="appointment-service-card h-100">
        {{-- Featured Badge --}}
        @if(!empty($show_featured_badge) && $appointment->is_featured === 'on')
            <span class="featured-badge">{{ __('Featured') }}</span>
        @endif
        
        {{-- Discount Badge --}}
        @if($has_discount)
            @php
                $discount_percent = round((($appointment->price - $appointment->sale_price) / $appointment->price) * 100);
            @endphp
            <span class="discount-badge">-{{ $discount_percent }}%</span>
        @endif

        {{-- Service Image --}}
        @if(($data['show_images'] ?? true) && $appointment->image)
            <div class="service-image-wrapper">
                <a href="{{ route('tenant.frontend.appointment.order.page', $appointment->slug) }}">
                    {!! render_image_markup_by_attachment_id($appointment->image, '', 'img-fluid w-100') !!}
                </a>
            </div>
        @endif

        <div class="service-content p-4">
            {{-- Subcategory Label --}}
            @if(!empty($show_subcategory) && isset($subcategory))
                <span class="subcategory-label text-muted small mb-2 d-block">
                    {{ $subcategory->getTranslation('title', $current_lang) }}
                </span>
            @endif

            {{-- Title --}}
            <h5 class="service-title mb-2">
                <a href="{{ route('tenant.frontend.appointment.order.page', $appointment->slug) }}">
                    {{ $appointment->getTranslation('title', $current_lang) }}
                </a>
            </h5>

            {{-- Short Description --}}
            @if($appointment->short_description)
                <p class="service-description text-muted small mb-3">
                    {{ \Illuminate\Support\Str::limit($appointment->getTranslation('short_description', $current_lang), 80) }}
                </p>
            @elseif($appointment->description)
                <p class="service-description text-muted small mb-3">
                    {{ \Illuminate\Support\Str::limit(strip_tags($appointment->getTranslation('description', $current_lang)), 80) }}
                </p>
            @endif

            {{-- Meta Info --}}
            <div class="service-meta d-flex flex-wrap gap-3 mb-3">
                {{-- Duration --}}
                @if(($data['show_duration'] ?? true) && $appointment->duration)
                    <span class="meta-item duration">
                        <i class="las la-clock"></i>
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

                {{-- Popular Badge --}}
                @if($appointment->is_popular === 'on')
                    <span class="meta-item popular">
                        <i class="las la-star"></i> {{ __('Popular') }}
                    </span>
                @endif
            </div>

            {{-- Price & Book Button --}}
            <div class="service-footer d-flex justify-content-between align-items-center mt-auto">
                @if($data['show_prices'] ?? true)
                    <div class="price-wrapper">
                        @if($has_discount)
                            <span class="original-price text-decoration-line-through text-muted small">
                                {{ amount_with_currency_symbol($appointment->price) }}
                            </span>
                        @endif
                        <span class="current-price fw-bold text-primary">
                            {{ amount_with_currency_symbol($effective_price) }}
                        </span>
                    </div>
                @endif
                
                <a href="{{ route('tenant.frontend.appointment.order.page', $appointment->slug) }}" class="btn btn-sm btn-primary book-btn">
                    {{ $data['book_now_text'] ?? __('Book Now') }}
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Appointment Card - Theme Compatible */
.appointment-service-card {
    background: var(--section-bg, #fff);
    border-radius: var(--card-radius, 10px);
    overflow: hidden;
    box-shadow: var(--card-shadow, 0 2px 10px rgba(0,0,0,0.08));
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    flex-direction: column;
}
.appointment-service-card:hover {
    transform: translateY(-5px);
}
.appointment-service-card .featured-badge,
.appointment-service-card .discount-badge {
    position: absolute;
    top: 15px;
    padding: 5px 12px;
    border-radius: var(--btn-radius, 5px);
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 2;
}
.appointment-service-card .featured-badge {
    left: 15px;
    background: var(--secondary-color, #f39c12);
    color: #fff;
}
.appointment-service-card .discount-badge {
    right: 15px;
    background: var(--danger-color, #e74c3c);
    color: #fff;
}
.appointment-service-card .service-image-wrapper {
    height: 200px;
    overflow: hidden;
}
.appointment-service-card .service-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}
.appointment-service-card:hover .service-image-wrapper img {
    transform: scale(1.05);
}
.appointment-service-card .service-content {
    flex: 1;
    display: flex;
    flex-direction: column;
}
.appointment-service-card .service-title {
    font-family: var(--heading-font, inherit);
}
.appointment-service-card .service-title a {
    color: var(--heading-color, inherit);
    text-decoration: none;
    transition: color 0.3s ease;
}
.appointment-service-card .service-title a:hover {
    color: var(--main-color-one, var(--bs-primary, inherit));
}
.appointment-service-card .service-description {
    font-family: var(--body-font, inherit);
}
.appointment-service-card .service-meta .meta-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.85rem;
    color: var(--paragraph-color, #666);
}
.appointment-service-card .service-meta .popular {
    color: var(--secondary-color, #f39c12);
}
.appointment-service-card .current-price {
    font-size: 1.25rem;
    color: var(--main-color-one, var(--bs-primary, inherit));
}
.appointment-service-card .book-btn {
    border-radius: var(--btn-radius, 5px);
    padding: 8px 20px;
    background-color: var(--main-color-one, var(--bs-primary));
    border-color: var(--main-color-one, var(--bs-primary));
}
.appointment-service-card .book-btn:hover {
    opacity: 0.9;
}
</style>
