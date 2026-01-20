<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Middleware\Tenant\InitializeTenancyByDomainCustomisedMiddleware;
use Illuminate\Support\Facades\Route;
use Modules\IyzipayPaymentGateway\Http\Controllers\IyzipayPaymentGatewayController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

// Landlord
Route::middleware(['auth:admin','adminglobalVariable', 'setlang'])
    ->prefix('admin-home/landlord/payment-settings/plugin/iyzipay')
    ->name('landlord.admin.payment.settings.iyzipay.')
    ->group(function () {
        Route::get("/", [IyzipayPaymentGatewayController::class, "settings"])->name("settings");
        Route::post("/update", [IyzipayPaymentGatewayController::class, "settingsUpdate"])->name('settings-update');
    });

Route::middleware(['landlord_glvar','maintenance_mode','setlang'])->name('landlord.')->group(function () {
    Route::post('landlord/plugin/iyzipay/ipn', [IyzipayPaymentGatewayController::class, 'iyzipay_ipn'])->name('plugin.iyzipay.ipn');
});


// Tenant
Route::middleware([
    'web',
    InitializeTenancyByDomainCustomisedMiddleware::class,
    PreventAccessFromCentralDomains::class,
    'auth:admin',
    'tenant_admin_glvar',
    'package_expire',
    'tenantAdminPanelMailVerify',
    'setlang'
])->prefix('admin-home/payment-settings/plugin/iyzipay')
    ->name('tenant.admin.payment.settings.iyzipay.')
    ->group(function () {
        Route::get("/", [IyzipayPaymentGatewayController::class, "settings"])->name("settings");
        Route::post("/update", [IyzipayPaymentGatewayController::class, "settingsUpdate"])->name('settings-update');
});

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'tenant_glvar',
    'maintenance_mode',
    'setlang'
])->name('tenant.')->group(function () {
    Route::post('tenant/plugin/iyzipay/ipn', [IyzipayPaymentGatewayController::class, 'iyzipay_ipn'])->name('plugin.iyzipay.ipn');
});
