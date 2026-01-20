<?php

return [

    'enabled' => env('ANALYTICS_ENABLED', true),

    /**
     * Analytics Dashboard.
     *
     * The prefix and middleware for the analytics dashboard.
     */
    'prefix' => 'analytics',

    'middleware' => [
        'web',
    ],

    /**
     * Exclude.
     *
     * The routes excluded from page view tracking.
     */
    'exclude' => [
        '/analytics',
        '/analytics/*',
        '/assets/theme/screenshot/hotel-booking/*',
        '/token-wise-login/*',
        '/user-home',
        '/user-home/*',
        '/package-orders',
        '/custom-domain',
        '/landlord/wallet*',
        '/user/*',
        '/request-refund/*',
        '/admin-home',
        '/admin-home/*',
        '/favicon.ico',
        '/shop/order-success',
        '/shop/order-success/*',
        '/shop/order-cancel',
        '/shop/order-cancel/*',
        '/shop/*-ipn',
        '/shop/checkout/shipping-method-data',
        '/shop/assets/*',
        '/assets/*',
        '/order-success/*',
        '/order-cancel/*',
        '/unique-checker',
        '/stripe-ipn',
        '/mollie-ipn',
        '/flutterwave/ipn',
        '/paystack-ipn',
        '/midtrans-ipn',
        '/instamojo-ipn',
        '/paypal-ipn',
        '/marcadopago-ipn',
        '/squareup-ipn',
        '/telescope',
        '/telescope/*',
        '/login/reset-password/*',
        '/token-login',
        '/admin',
        '/apply-coupon'
    ],

    /**
     * Determine if traffic from robots should be tracked.
     */
    'ignoreRobots' => false,

    /**
     * Ignored IP addresses.
     *
     * The IP addresses excluded from page view tracking.
     */
    'ignoredIPs' => [
        // '192.168.1.1',
    ],

    /**
     * Mask.
     *
     * Mask routes so they are tracked together.
     */
    'mask' => [
        // '/users/*',
    ],

    /**
     * Ignore methods.
     *
     * The HTTP verbs/methods that should be excluded from page view tracking.
     */
    'ignoreMethods' => [
         'OPTIONS', 'POST',
    ],

    'session' => [
        'provider' => \AndreasElia\Analytics\RequestSessionProvider::class,
    ],

];
