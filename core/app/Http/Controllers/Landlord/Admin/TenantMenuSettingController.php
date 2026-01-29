<?php

namespace App\Http\Controllers\Landlord\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantMenuSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TenantMenuSettingController extends Controller
{
    /**
     * Display the menu settings page for a specific tenant (store).
     * Each tenant/store has its own independent settings.
     *
     * @param string $tenantId
     * @return \Illuminate\Contracts\View\View
     */
    public function index(string $tenantId)
    {
        $tenant = Tenant::with(['user', 'domain'])->findOrFail($tenantId);
        
        // Get all available menu items
        $menuItems = $this->getAllMenuItems();
        
        // Get current settings for this tenant
        $currentSettings = TenantMenuSetting::where('tenant_id', $tenantId)
            ->pluck('is_visible', 'menu_key')
            ->toArray();

        return view('landlord.admin.tenant-menu.index', [
            'tenant' => $tenant,
            'menuItems' => $menuItems,
            'currentSettings' => $currentSettings,
        ]);
    }

    /**
     * Update menu settings for a tenant (store).
     *
     * @param Request $request
     * @param string $tenantId
     * @return JsonResponse
     */
    public function update(Request $request, string $tenantId): JsonResponse
    {
        $tenant = Tenant::findOrFail($tenantId);

        $request->validate([
            'menu_settings' => 'required|array',
            'menu_settings.*.menu_key' => 'required|string',
            'menu_settings.*.is_visible' => 'required',
            'menu_settings.*.menu_label' => 'nullable|string',
            'menu_settings.*.parent_key' => 'nullable|string',
        ]);

        foreach ($request->menu_settings as $setting) {
            // Convert is_visible to boolean properly
            $isVisible = filter_var($setting['is_visible'], FILTER_VALIDATE_BOOLEAN);
            
            TenantMenuSetting::setMenuVisibility(
                $tenantId,
                $setting['menu_key'],
                $isVisible,
                $setting['menu_label'] ?? null,
                $setting['parent_key'] ?? null
            );
        }

        return response()->json([
            'success' => true,
            'message' => __('Menu settings updated successfully'),
        ]);
    }

    /**
     * Toggle a single menu item visibility for a tenant.
     *
     * @param Request $request
     * @param string $tenantId
     * @return JsonResponse
     */
    public function toggle(Request $request, string $tenantId): JsonResponse
    {
        $tenant = Tenant::findOrFail($tenantId);

        $request->validate([
            'menu_key' => 'required|string',
            'is_visible' => 'required',
            'menu_label' => 'nullable|string',
            'parent_key' => 'nullable|string',
        ]);

        // Convert is_visible to boolean properly
        $isVisible = filter_var($request->is_visible, FILTER_VALIDATE_BOOLEAN);
        
        $setting = TenantMenuSetting::setMenuVisibility(
            $tenantId,
            $request->menu_key,
            $isVisible,
            $request->menu_label,
            $request->parent_key
        );

        return response()->json([
            'success' => true,
            'message' => __('Menu visibility updated'),
            'data' => $setting,
        ]);
    }

    /**
     * Reset all menu settings for a tenant to default.
     *
     * @param string $tenantId
     * @return JsonResponse
     */
    public function reset(string $tenantId): JsonResponse
    {
        $tenant = Tenant::findOrFail($tenantId);

        $deletedCount = TenantMenuSetting::resetToDefault($tenantId);

        return response()->json([
            'success' => true,
            'message' => __('Menu settings reset to default'),
            'deleted_count' => $deletedCount,
        ]);
    }

    /**
     * Get all available menu items structure.
     * This returns a hierarchical array of all possible sidebar menu items.
     *
     * @return array
     */
    private function getAllMenuItems(): array
    {
        return [
            // Dashboard
            [
                'key' => 'tenant-dashboard-menu',
                'label' => __('Dashboard'),
                'icon' => 'mdi mdi-view-dashboard',
                'feature' => 'dashboard',
                'children' => [],
            ],
            
            // Staff Role Manage
            [
                'key' => 'admin-manage-settings-menu-items',
                'label' => __('Staff Role Manage'),
                'icon' => 'mdi mdi-account-supervisor',
                'feature' => 'admin',
                'children' => [
                    ['key' => 'admins-manage-settings-list-menu-items', 'label' => __('All Staff')],
                    ['key' => 'admins-manage-settings-add-new-menu-items', 'label' => __('Add New Staff')],
                    ['key' => 'admins-role-manage-settings-add-new-menu-items', 'label' => __('All Staff Role')],
                ],
            ],
            
            // Users Manage
            [
                'key' => 'users-manage-settings-menu-items',
                'label' => __('Users Manage'),
                'icon' => 'mdi mdi-account-group',
                'feature' => 'user',
                'children' => [
                    ['key' => 'users-manage-settings-list-menu-items', 'label' => __('All Users')],
                    ['key' => 'users-manage-settings-add-new-menu-items', 'label' => __('Add New')],
                ],
            ],
            
            // Product Order Manage
            [
                'key' => 'product-order-manage-settings',
                'label' => __('Product Order Manage'),
                'icon' => 'mdi mdi-cart',
                'feature' => 'eCommerce',
                'plugin' => 'Product',
                'children' => [
                    ['key' => 'product-order-manage-settings-all-order', 'label' => __('All Order')],
                    ['key' => 'product-order-manage-settings-success-page', 'label' => __('Success Order Page')],
                    ['key' => 'product-order-manage-settings-cancel-page', 'label' => __('Cancel Order Page')],
                    ['key' => 'product-order-manage-settings-order-settings', 'label' => __('Order Settings')],
                ],
            ],
            
            // Badge Manage
            [
                'key' => 'badge-settings-menu-items',
                'label' => __('Badge Manage'),
                'icon' => 'mdi mdi-certificate',
                'feature' => 'eCommerce',
                'plugin' => 'Badge',
                'children' => [
                    ['key' => 'badge-all-settings-menu-items', 'label' => __('Badge Manage')],
                ],
            ],
            
            // Country Manage
            [
                'key' => 'country-settings-menu-items',
                'label' => __('Country Manage'),
                'icon' => 'mdi mdi-earth',
                'feature' => 'eCommerce',
                'children' => [
                    ['key' => 'country-all-settings-menu-items', 'label' => __('Country Manage')],
                    ['key' => 'state-all-settings-menu-items', 'label' => __('State Manage')],
                ],
            ],
            
            // Tax Manage
            [
                'key' => 'tax-settings-menu-items',
                'label' => __('Tax Manage'),
                'icon' => 'mdi mdi-percent',
                'feature' => 'eCommerce',
                'children' => [
                    ['key' => 'tax-country-settings-menu-items', 'label' => __('Country Tax Manage')],
                    ['key' => 'tax-state-settings-menu-items', 'label' => __('State Tax Manage')],
                ],
            ],
            
            // Shipping Manage
            [
                'key' => 'shipping-settings-menu-items',
                'label' => __('Shipping Manage'),
                'icon' => 'mdi mdi-truck',
                'feature' => 'eCommerce',
                'plugin' => 'ShippingModule',
                'children' => [
                    ['key' => 'shipping-zone-settings-menu-items', 'label' => __('Shipping Zone')],
                    ['key' => 'shipping-method-settings-menu-items', 'label' => __('Shipping Method')],
                ],
            ],
            
            // Coupon Manage
            [
                'key' => 'coupon-settings-menu-items',
                'label' => __('Coupon Manage'),
                'icon' => 'mdi mdi-ticket-percent',
                'feature' => 'eCommerce',
                'plugin' => 'CouponManage',
                'children' => [
                    ['key' => 'product-coupon-settings-menu-items', 'label' => __('All Coupon')],
                ],
            ],
            
            // Attribute
            [
                'key' => 'product-attribute-menu-items',
                'label' => __('Attribute'),
                'icon' => 'mdi mdi-format-list-bulleted',
                'feature' => 'eCommerce',
                'plugin' => 'Attributes',
                'children' => [
                    ['key' => 'product-category-settings-menu-items', 'label' => __('Category Manage')],
                    ['key' => 'product-sub-category-settings-menu-items', 'label' => __('Subcategory Manage')],
                    ['key' => 'product-child-category-settings-menu-items', 'label' => __('Child Category Manage')],
                    ['key' => 'product-tag-settings-menu-items', 'label' => __('Tags Manage')],
                    ['key' => 'product-unit-settings-menu-items', 'label' => __('Unit Manage')],
                    ['key' => 'product-color-settings-menu-items', 'label' => __('Color Manage')],
                    ['key' => 'product-size-settings-menu-items', 'label' => __('Size Manage')],
                    ['key' => 'product-brand-settings-menu-items', 'label' => __('Product Brand Manage')],
                    ['key' => 'delivery-option-settings-menu-items', 'label' => __('Delivery Option Manage')],
                    ['key' => 'product-delivery-option-settings-menu-items', 'label' => __('Product Attribute')],
                ],
            ],
            
            // Products
            [
                'key' => 'product-settings-menu-items',
                'label' => __('Products'),
                'icon' => 'mdi mdi-package-variant',
                'feature' => 'product',
                'plugin' => 'Product',
                'children' => [
                    ['key' => 'product-all-settings-menu-items', 'label' => __('All Products')],
                    ['key' => 'product-create-menu-items', 'label' => __('Add New Product')],
                ],
            ],
            
            // Inventory
            [
                'key' => 'inventory-settings-menu-items',
                'label' => __('Inventory'),
                'icon' => 'mdi mdi-warehouse',
                'feature' => 'inventory',
                'plugin' => 'Inventory',
                'children' => [
                    ['key' => 'inventory-manage-settings-menu-items', 'label' => __('Inventory Manage')],
                    ['key' => 'inventory-stock-settings-menu-items', 'label' => __('Inventory Settings')],
                ],
            ],
            
            // Campaign
            [
                'key' => 'campaign-settings-menu-items',
                'label' => __('Campaign'),
                'icon' => 'mdi mdi-bullhorn',
                'feature' => 'campaign',
                'plugin' => 'Campaign',
                'children' => [],
            ],
            
            // Donations
            [
                'key' => 'donation-settings-menu-items',
                'label' => __('Donations'),
                'icon' => 'mdi mdi-hand-heart',
                'feature' => 'donation',
                'plugin' => 'Donation',
                'children' => [
                    ['key' => 'donation-settings-all-page-settings', 'label' => __('All Donations')],
                    ['key' => 'donation-settings-add-page-settings', 'label' => __('Add Donation')],
                    ['key' => 'donation-settings-category-page-settings', 'label' => __('Category')],
                    ['key' => 'donation-settings-all-donations-logs', 'label' => __('All Payment Logs')],
                    ['key' => 'donation-settings-payment-logs-report', 'label' => __('Payment Logs Report')],
                    ['key' => 'donation-settings-donations-all-settings', 'label' => __('Settings')],
                ],
            ],
            
            // Events
            [
                'key' => 'event-settings-menu-items',
                'label' => __('Events'),
                'icon' => 'mdi mdi-calendar-star',
                'feature' => 'event',
                'plugin' => 'Event',
                'children' => [
                    ['key' => 'event-settings-all-page-settings', 'label' => __('All Event')],
                    ['key' => 'event-settings-add-page-settings', 'label' => __('Add Event')],
                    ['key' => 'event-settings-category-page-settings', 'label' => __('Category')],
                    ['key' => 'event-settings-all-donations-logs', 'label' => __('All Payment Logs')],
                    ['key' => 'event-settings-payment-logs-report', 'label' => __('Payment Logs Report')],
                    ['key' => 'event-settings-event-all-settings', 'label' => __('Settings')],
                ],
            ],
            
            // Jobs
            [
                'key' => 'job-settings-menu-items',
                'label' => __('Jobs'),
                'icon' => 'mdi mdi-briefcase',
                'feature' => 'job',
                'plugin' => 'Job',
                'children' => [
                    ['key' => 'job-settings-all-page-settings', 'label' => __('All Jobs')],
                    ['key' => 'job-settings-add-page-settings', 'label' => __('Add Job')],
                    ['key' => 'job-settings-category-page-settings', 'label' => __('Category')],
                    ['key' => 'job-settings-all-paid-logs', 'label' => __('All Paid Applications')],
                    ['key' => 'job-settings-all-unpaid-logs', 'label' => __('All Unpaid Applications')],
                    ['key' => 'job-settings-payment-logs-report', 'label' => __('Payment Job Report')],
                    ['key' => 'job-settings-job-settings', 'label' => __('Settings')],
                ],
            ],
            
            // Appointment
            [
                'key' => 'appointment-settings-menu-items',
                'label' => __('Appointment'),
                'icon' => 'mdi mdi-calendar-clock',
                'feature' => 'appointment',
                'plugin' => 'Appointment',
                'children' => [
                    ['key' => 'appointment-services-all-page-settings', 'label' => __('All Appointments')],
                    ['key' => 'appointment-categories-all-page-settings', 'label' => __('Categories')],
                    ['key' => 'appointment-sub-categories-all-page-settings', 'label' => __('Sub-categories')],
                    ['key' => 'appointment-sub-servicess-all-page-settings', 'label' => __('All Sub Appointments')],
                    ['key' => 'appointment-day-types-page-settings', 'label' => __('Appointment Day Types')],
                    ['key' => 'appointment-days-page-settings', 'label' => __('Appointment Days')],
                    ['key' => 'appointment-schedules-page-settings', 'label' => __('Appointment Schedules')],
                    ['key' => 'appointment-complete-logs', 'label' => __('All Payment Logs')],
                    ['key' => 'appointment-logs-report', 'label' => __('Appointment Report')],
                    ['key' => 'appointment-settings', 'label' => __('Settings')],
                ],
            ],
            
            // Support Tickets
            [
                'key' => 'support-tickets-settings-menu-items',
                'label' => __('Support Tickets'),
                'icon' => 'mdi mdi-ticket-account',
                'feature' => 'support_ticket',
                'plugin' => 'SupportTicket',
                'children' => [
                    ['key' => 'support-ticket-settings-all', 'label' => __('All Tickets')],
                    ['key' => 'support-ticket-settings-add', 'label' => __('Add New Ticket')],
                    ['key' => 'support-ticket-settings-department', 'label' => __('Departments')],
                    ['key' => 'support-ticket-settings-setting', 'label' => __('Page Settings')],
                ],
            ],
            
            // Blogs
            [
                'key' => 'blog-settings-menu-items',
                'label' => __('Blogs'),
                'icon' => 'mdi mdi-post',
                'feature' => 'blog',
                'children' => [
                    ['key' => 'blog-all-settings-menu-items', 'label' => __('All Blogs')],
                    ['key' => 'blog-add-settings-menu-items', 'label' => __('Add New Blog')],
                    ['key' => 'blog-category-settings-all', 'label' => __('Blog Category')],
                    ['key' => 'blog-settings-all', 'label' => __('Settings')],
                ],
            ],
            
            // Pages
            [
                'key' => 'pages-settings-menu-items',
                'label' => __('Pages'),
                'icon' => 'mdi mdi-file-document',
                'feature' => 'page',
                'children' => [
                    ['key' => 'pages-settings-all-page-settings', 'label' => __('All Pages')],
                    ['key' => 'pages-settings-new-page-settings', 'label' => __('New Pages')],
                ],
            ],
            
            // Portfolio
            [
                'key' => 'portfolio-settings-menu-items',
                'label' => __('Portfolio'),
                'icon' => 'mdi mdi-folder-image',
                'feature' => 'portfolio',
                'plugin' => 'Portfolio',
                'children' => [
                    ['key' => 'settings-portfolio-list', 'label' => __('All Portfolio')],
                    ['key' => 'settings-portfolio-add', 'label' => __('Add Portfolio')],
                    ['key' => 'settings-portfolio-category', 'label' => __('Category')],
                ],
            ],
            
            // Services
            [
                'key' => 'services-settings-menu-items',
                'label' => __('Services'),
                'icon' => 'mdi mdi-cog',
                'feature' => 'service',
                'plugin' => 'Service',
                'children' => [
                    ['key' => 'services-settings-all-page-settings', 'label' => __('All Services')],
                    ['key' => 'services-settings-add-page-settings', 'label' => __('Add Service')],
                    ['key' => 'services-settings-category-page-settings', 'label' => __('Category')],
                ],
            ],
            
            // Knowledgebase
            [
                'key' => 'knowledgebase-settings-menu-items',
                'label' => __('Knowledgebase'),
                'icon' => 'mdi mdi-book-open-page-variant',
                'feature' => 'knowledgebase',
                'plugin' => 'Knowledgebase',
                'children' => [
                    ['key' => 'settings-knowledgebase-list', 'label' => __('All Knowledgebase')],
                    ['key' => 'settings-knowledgebase-add', 'label' => __('Add Knowledgebase')],
                    ['key' => 'settings-knowledgebase-category', 'label' => __('Category')],
                ],
            ],
            
            // Newsletter
            [
                'key' => 'newsletter',
                'label' => __('Newsletter Manage'),
                'icon' => 'mdi mdi-email-newsletter',
                'feature' => 'newsletter',
                'plugin' => 'NewsLetter',
                'children' => [
                    ['key' => 'all-newsletter', 'label' => __('All Subscribers')],
                    ['key' => 'mail-send-all-newsletter', 'label' => __('Send Mail to All')],
                ],
            ],
            
            // FAQs
            [
                'key' => 'faq-settings-menu-items',
                'label' => __('Faqs'),
                'icon' => 'mdi mdi-frequently-asked-questions',
                'feature' => 'faq',
                'children' => [
                    ['key' => 'fall-all-list', 'label' => __('All Faq')],
                    ['key' => 'faq-category', 'label' => __('Category')],
                ],
            ],
            
            // Form Builder
            [
                'key' => 'form-builder-settings-menu-items',
                'label' => __('Form Builder'),
                'icon' => 'mdi mdi-form-select',
                'feature' => 'form_builder',
                'children' => [
                    ['key' => 'form-builder-settings-all', 'label' => __('Custom Form Builder')],
                    ['key' => 'form-builder-settings-contact-message', 'label' => __('All Form Submission')],
                ],
            ],
            
            // Brands
            [
                'key' => 'brands',
                'label' => __('Brands'),
                'icon' => 'mdi mdi-tag',
                'feature' => 'brand',
                'children' => [],
            ],
            
            // Custom Domain
            [
                'key' => 'custom-domain-request',
                'label' => __('Custom Domain'),
                'icon' => 'mdi mdi-web',
                'feature' => 'custom_domain',
                'children' => [],
            ],
            
            // Testimonial
            [
                'key' => 'testimonial',
                'label' => __('Testimonial'),
                'icon' => 'mdi mdi-comment-quote',
                'feature' => 'testimonial',
                'children' => [],
            ],
            
            // Advertisement
            [
                'key' => 'advertisement-settings-menu-items',
                'label' => __('Advertisement'),
                'icon' => 'mdi mdi-advertisements',
                'feature' => 'advertisement',
                'children' => [
                    ['key' => 'advertisement-all-settings-menu-items', 'label' => __('All Advertise')],
                    ['key' => 'advertisement-add-settings-menu-items', 'label' => __('Add Advertise')],
                ],
            ],
            
            // Image Gallery
            [
                'key' => 'image_gallery-settings-menu-items',
                'label' => __('Image Gallery'),
                'icon' => 'mdi mdi-image-multiple',
                'feature' => 'gallery',
                'children' => [
                    ['key' => 'image-gallery-list', 'label' => __('All Gallery')],
                    ['key' => 'image-gallery-category', 'label' => __('Category')],
                ],
            ],
            
            // Price Plan
            [
                'key' => 'wedding-price-plan-settings-menu-items',
                'label' => __('Price Plan'),
                'icon' => 'mdi mdi-currency-usd',
                'feature' => 'wedding_price_plan',
                'children' => [
                    ['key' => 'all-wedding-list', 'label' => __('All Plans')],
                    ['key' => 'all-wedding-plan-payment-logs', 'label' => __('Payment Logs')],
                ],
            ],
            
            // My Package Orders
            [
                'key' => 'tenant-payment-manage-settings-menu-items',
                'label' => __('My Package Orders'),
                'icon' => 'mdi mdi-package',
                'feature' => 'own_order_manage',
                'children' => [
                    ['key' => 'my-payment-manage-my-logs-settings-menu-items', 'label' => __('My Payment Logs')],
                ],
            ],
            
            // Appearance Settings
            [
                'key' => 'appearance-settings-menu-items',
                'label' => __('Appearance Settings'),
                'icon' => 'mdi mdi-palette',
                'feature' => 'appearance_settings',
                'children' => [
                    ['key' => 'theme-settings-all-tenant', 'label' => __('Theme Manage')],
                    ['key' => 'menu-settings-all', 'label' => __('Menu Manage')],
                    ['key' => 'widget-builder-settings-all', 'label' => __('Widget Builder')],
                    ['key' => 'topbar-settings-all', 'label' => __('Topbar Settings')],
                    ['key' => 'other-settings', 'label' => __('Other Settings')],
                    ['key' => '404-settings-all', 'label' => __('404 Settings')],
                    ['key' => 'maintenance-settings-all', 'label' => __('Maintenance Settings')],
                ],
            ],
            
            // General Settings
            [
                'key' => 'general-settings-menu-items',
                'label' => __('General Settings'),
                'icon' => 'mdi mdi-cog',
                'feature' => 'general_settings',
                'children' => [
                    ['key' => 'general-settings-reading-settings', 'label' => __('Page Settings')],
                    ['key' => 'general-settings-site-identity', 'label' => __('Site Identity')],
                    ['key' => 'general-settings-basic-settings', 'label' => __('Basic Settings')],
                    ['key' => 'general-settings-color-settings', 'label' => __('Color Settings')],
                    ['key' => 'general-settings-typography-settings', 'label' => __('Typography Settings')],
                    ['key' => 'general-settings-seo-settings', 'label' => __('SEO Settings')],
                    ['key' => 'general-settings-third-party-script-settings', 'label' => __('Third Party Script')],
                    ['key' => 'general-settings-email-settings', 'label' => __('Email Settings')],
                    ['key' => 'general-settings-custom-css-settings', 'label' => __('Custom CSS')],
                    ['key' => 'general-settings-custom-js-settings', 'label' => __('Custom JS')],
                    ['key' => 'general-settings-cache-settings', 'label' => __('Cache Settings')],
                    ['key' => 'general-settings-gdpr-settings', 'label' => __('GDPR Settings')],
                    ['key' => 'general-settings-sitemap-settings', 'label' => __('Sitemap Settings')],
                ],
            ],
            
            // Payment Settings
            [
                'key' => 'payment_gateway-manage-settings-menu-items',
                'label' => __('Payment Settings'),
                'icon' => 'mdi mdi-credit-card',
                'feature' => 'payment_gateways',
                'children' => [
                    ['key' => 'currency-settings-list-menu-items', 'label' => __('Currencies')],
                    ['key' => 'paypal-settings-list-menu-items', 'label' => __('Paypal')],
                    ['key' => 'stripe-settings-list-menu-items', 'label' => __('Stripe')],
                    ['key' => 'razorpay-settings-list-menu-items', 'label' => __('Razorpay')],
                    ['key' => 'paystack-settings-list-menu-items', 'label' => __('Paystack')],
                    ['key' => 'mollie-settings-list-menu-items', 'label' => __('Mollie')],
                    ['key' => 'flutterwave-settings-list-menu-items', 'label' => __('Flutterwave')],
                    ['key' => 'paytm-settings-list-menu-items', 'label' => __('Paytm')],
                    ['key' => 'manual_payment-settings-list-menu-items', 'label' => __('Manual Payment')],
                    ['key' => 'bank-transfer-settings-list-menu-items', 'label' => __('Bank Transfer')],
                ],
            ],
            
            // Languages
            [
                'key' => 'tenant-languages',
                'label' => __('Languages'),
                'icon' => 'mdi mdi-translate',
                'feature' => 'language',
                'children' => [],
            ],
            
            // Hotel Booking (External Module)
            [
                'key' => 'hotel-bookings-menu',
                'label' => __('Booking Services'),
                'icon' => 'mdi mdi-bed',
                'feature' => 'hotelbooking',
                'plugin' => 'HotelBooking',
                'children' => [
                    ['key' => 'hotel-manage-hotel-all-menu', 'label' => __('All Booking')],
                    ['key' => 'hotel-manage-room-type-all-menu', 'label' => __('All Types')],
                    ['key' => 'hotel-manage-room-all-menu', 'label' => __('All Items')],
                    ['key' => 'hotel-manage-bed-type-all-menu', 'label' => __('All Category Types')],
                    ['key' => 'room-booking-inventories-menu', 'label' => __('Book Inventories')],
                    ['key' => 'hotel-manage-reviews-menu', 'label' => __('Reviews')],
                ],
            ],
            
            // Restaurant (External Module)
            [
                'key' => 'restaurant-menu',
                'label' => __('Restaurant'),
                'icon' => 'mdi mdi-food',
                'feature' => 'restaurant',
                'plugin' => 'Restaurant',
                'children' => [],
            ],
        ];
    }
}
