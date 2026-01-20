<?php

use App\Http\Middleware\Tenant\InitializeTenancyByDomainCustomisedMiddleware;
use Modules\SiteAnalytics\Http\Controllers\Admin\SiteAnalyticsSettingsController;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

// LANDLORD ADMIN
Route::middleware(['auth:admin', 'adminglobalVariable'])
    ->prefix('admin-home/site-analytics')
    ->name('landlord.')
    ->controller(SiteAnalyticsSettingsController::class)
    ->group(function () {
        Route::get( '/', 'index')->name('admin.dashboard.analytics');
        Route::get( '/analytics', 'analytics')->name('admin.analytics');
        Route::get('/settings', 'settings')->name('admin.analytics.settings');
        Route::post('/settings', 'update_settings');
    });


// TENANT ADMIN
Route::group(['middleware' => [
    'auth:admin', 'adminglobalVariable',
    InitializeTenancyByDomainCustomisedMiddleware::class,
    PreventAccessFromCentralDomains::class,
], 'prefix' => 'admin-home/tenant-site-analytics/', 'as' => 'tenant.'], function () {
    Route::controller(SiteAnalyticsSettingsController::class)->group( function () {
        Route::get( '/', 'index')->name('admin.dashboard.analytics');
        Route::get( '/analytics', 'analytics')->name('admin.analytics');
        Route::get('/settings', 'settings')->name('admin.analytics.settings');
        Route::post('/settings', 'update_settings');
    });
});
