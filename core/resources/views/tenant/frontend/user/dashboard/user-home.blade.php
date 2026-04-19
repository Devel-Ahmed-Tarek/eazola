@extends('tenant.frontend.user.dashboard.user-master')

@section('style')
    @parent
    <link rel="stylesheet" href="{{ global_asset('assets/tenant/frontend/css/user-dashboard-home.css') }}">
@endsection

@section('page-title')
    {{ __('User Dashboard') }}
@endsection

@section('title')
    {{ __('User Dashboard') }}
@endsection

@section('section')
    @php
        $authUser = Auth::guard('web')->user();
        $restaurant = is_array($restaurant_orders ?? null) ? $restaurant_orders : [];
        $hasRestaurantStats = is_array($restaurant_orders ?? null) && collect($restaurant_orders)->sum() > 0;
    @endphp

    <div class="user-dashboard-home">
        <div class="ud-welcome">
            <div class="ud-welcome-inner">
                <h1>{{ __('Hello, :name', ['name' => $authUser->name ?? __('User')]) }}</h1>
                <p>{{ __('Here is an overview of your activity and orders.') }}</p>
                <div class="ud-date">
                    <i class="las la-calendar-alt"></i>
                    {{ \Carbon\Carbon::now()->locale(app()->getLocale())->isoFormat('dddd, D MMMM YYYY') }}
                </div>
            </div>
        </div>

        <h2 class="ud-section-title">
            <i class="las la-chart-pie"></i>
            {{ __('Overview') }}
        </h2>

        <div class="row g-4 mb-2">
            <div class="col-xl-4 col-md-6">
                <div class="ud-stat-card">
                    <div class="ud-stat-card__top">
                        <div>
                            <p class="ud-stat-card__value">{{ $total_donation ?? 0 }}</p>
                            <p class="ud-stat-card__label">{{ __('Total Donation') }}</p>
                        </div>
                        <div class="ud-stat-card__icon ud-stat-card__icon--a" aria-hidden="true">
                            <i class="las la-hand-holding-heart"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="ud-stat-card">
                    <div class="ud-stat-card__top">
                        <div>
                            <p class="ud-stat-card__value">{{ $total_product ?? 0 }}</p>
                            <p class="ud-stat-card__label">{{ __('Total Product') }}</p>
                        </div>
                        <div class="ud-stat-card__icon ud-stat-card__icon--b" aria-hidden="true">
                            <i class="las la-shopping-bag"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="ud-stat-card">
                    <div class="ud-stat-card__top">
                        <div>
                            <p class="ud-stat-card__value">{{ $total_event }}</p>
                            <p class="ud-stat-card__label">{{ __('Total Events') }}</p>
                        </div>
                        <div class="ud-stat-card__icon ud-stat-card__icon--c" aria-hidden="true">
                            <i class="las la-calendar-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="ud-stat-card">
                    <div class="ud-stat-card__top">
                        <div>
                            <p class="ud-stat-card__value">{{ $support_tickets ?? 0 }}</p>
                            <p class="ud-stat-card__label">{{ __('Support Tickets') }}</p>
                        </div>
                        <div class="ud-stat-card__icon ud-stat-card__icon--d" aria-hidden="true">
                            <i class="las la-headset"></i>
                        </div>
                    </div>
                </div>
            </div>

            @if($job_applications)
                <div class="col-xl-4 col-md-6">
                    <div class="ud-stat-card">
                        <div class="ud-stat-card__top">
                            <div>
                                <p class="ud-stat-card__value">{{ $job_applications }}</p>
                                <p class="ud-stat-card__label">{{ __('Applied Jobs') }}</p>
                            </div>
                            <div class="ud-stat-card__icon ud-stat-card__icon--a" aria-hidden="true">
                                <i class="las la-briefcase"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($wedding_plans)
                <div class="col-xl-4 col-md-6">
                    <div class="ud-stat-card">
                        <div class="ud-stat-card__top">
                            <div>
                                <p class="ud-stat-card__value">{{ $wedding_plans }}</p>
                                <p class="ud-stat-card__label">{{ __('Wedding Orders') }}</p>
                            </div>
                            <div class="ud-stat-card__icon ud-stat-card__icon--c" aria-hidden="true">
                                <i class="las la-ring"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($total_appointment)
                <div class="col-xl-4 col-md-6">
                    <div class="ud-stat-card">
                        <div class="ud-stat-card__top">
                            <div>
                                <p class="ud-stat-card__value">{{ $total_appointment }}</p>
                                <p class="ud-stat-card__label">{{ __('Total Appointment') }}</p>
                            </div>
                            <div class="ud-stat-card__icon ud-stat-card__icon--b" aria-hidden="true">
                                <i class="las la-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if(moduleExists('HotelBooking'))
            <h2 class="ud-section-title mt-5">
                <i class="las la-hotel"></i>
                {{ __('Hotel bookings') }}
            </h2>
            <div class="ud-mini-grid mb-4">
                @if($hotel_bookings['pending_reservations'] ?? null)
                    <div class="ud-mini-card">
                        <div class="ud-mini-card__text">
                            <span>{{ __('Pending reservation') }}</span>
                            <strong>{{ $hotel_bookings['pending_reservations'] }}</strong>
                        </div>
                        <div class="ud-mini-card__ico"><i class="las la-history"></i></div>
                    </div>
                @endif
                @if($hotel_bookings['accepted_reservations'] ?? null)
                    <div class="ud-mini-card">
                        <div class="ud-mini-card__text">
                            <span>{{ __('Accepted reservation') }}</span>
                            <strong>{{ $hotel_bookings['accepted_reservations'] }}</strong>
                        </div>
                        <div class="ud-mini-card__ico"><i class="las la-check-circle"></i></div>
                    </div>
                @endif
                @if($hotel_bookings['cancled_reservations'] ?? null)
                    <div class="ud-mini-card">
                        <div class="ud-mini-card__text">
                            <span>{{ __('Cancelled reservation') }}</span>
                            <strong>{{ $hotel_bookings['cancled_reservations'] }}</strong>
                        </div>
                        <div class="ud-mini-card__ico"><i class="las la-times-circle"></i></div>
                    </div>
                @endif
            </div>

        @endif

        @if(moduleExists('Restaurant') && $hasRestaurantStats)
            <h2 class="ud-section-title mt-4">
                <i class="las la-utensils"></i>
                {{ __('Restaurant orders') }}
            </h2>
            <div class="ud-mini-grid mb-4">
                @if(!empty($restaurant['pending_orders']))
                    <div class="ud-mini-card">
                        <div class="ud-mini-card__text">
                            <span>{{ __('Pending menu orders') }}</span>
                            <strong>{{ $restaurant['pending_orders'] }}</strong>
                        </div>
                        <div class="ud-mini-card__ico"><i class="las la-clock"></i></div>
                    </div>
                @endif
                @if(!empty($restaurant['inprogress_orders']))
                    <div class="ud-mini-card">
                        <div class="ud-mini-card__text">
                            <span>{{ __('In progress menu orders') }}</span>
                            <strong>{{ $restaurant['inprogress_orders'] }}</strong>
                        </div>
                        <div class="ud-mini-card__ico"><i class="las la-spinner"></i></div>
                    </div>
                @endif
                @if(!empty($restaurant['accepted_orders']))
                    <div class="ud-mini-card">
                        <div class="ud-mini-card__text">
                            <span>{{ __('Approved menu orders') }}</span>
                            <strong>{{ $restaurant['accepted_orders'] }}</strong>
                        </div>
                        <div class="ud-mini-card__ico"><i class="las la-check"></i></div>
                    </div>
                @endif
                @if(!empty($restaurant['canceled_orders']))
                    <div class="ud-mini-card">
                        <div class="ud-mini-card__text">
                            <span>{{ __('Canceled menu orders') }}</span>
                            <strong>{{ $restaurant['canceled_orders'] }}</strong>
                        </div>
                        <div class="ud-mini-card__ico"><i class="las la-ban"></i></div>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
