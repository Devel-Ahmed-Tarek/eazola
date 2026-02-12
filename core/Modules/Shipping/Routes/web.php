<?php

use Illuminate\Support\Facades\Route;
use Modules\Shipping\Http\Controllers\Tenant\Admin\SideupSettingsController;
use Modules\Shipping\Http\Controllers\Tenant\Admin\SideupOrderController;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    \App\Http\Middleware\Tenant\InitializeTenancyByDomainCustomisedMiddleware::class,
    PreventAccessFromCentralDomains::class,
    'auth:admin',
    'tenant_admin_glvar',
    'package_expire',
    'tenantAdminPanelMailVerify',
    'setlang',
])->prefix('admin-home')->name('tenant.')->group(function () {
    Route::prefix('shipping')->name('admin.shipping.')->group(function () {
        Route::get('/sideup', [SideupSettingsController::class, 'edit'])->name('sideup.settings');
        Route::post('/sideup', [SideupSettingsController::class, 'update'])->name('sideup.settings.update');
        Route::post('/sideup/test', [SideupSettingsController::class, 'test'])->name('sideup.settings.test');

        // Create shipment for a specific product order
        Route::post('/sideup/orders/{order}/create-shipment', [SideupOrderController::class, 'createShipment'])
            ->name('sideup.order.create-shipment');
    });
});

