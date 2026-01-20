<?php

use App\Http\Middleware\Tenant\InitializeTenancyByDomainCustomisedMiddleware;
use Modules\DomainReseller\Http\Controllers\DomainResellerController;
use Modules\DomainReseller\Http\Controllers\PaymentLogController;
use Modules\DomainReseller\Http\Middleware\PreventDuplicatePayment;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    InitializeTenancyByDomainCustomisedMiddleware::class,
    PreventAccessFromCentralDomains::class,
    'auth:admin',
    'tenant_admin_glvar',
    'package_expire',
    'tenantAdminPanelMailVerify',
    'setlang'
])->prefix('admin-home/domain-reseller')->name('tenant.admin.domain-reseller.')->group(function () {
    Route::get("/", [DomainResellerController::class, "index"])->name("index");
    Route::get("/domain-list", [DomainResellerController::class, "domainList"])->name("list.domain");
    Route::get("/domain-list/failed", [DomainResellerController::class, "failedDomainList"])->name("list.domain.failed");
    Route::post("/search-domain", [DomainResellerController::class, "searchDomain"])->name("search.domain");
    Route::post("/select-domain", [DomainResellerController::class, "selectDomain"])->name("select.domain");
    Route::get("/domain", [DomainResellerController::class, "showCart"])->name("cart");
    Route::get("/domain/checkout", [DomainResellerController::class, "showCheckout"])->name("checkout");
//    Route::post("/domain/checkout", [DomainResellerController::class, "submitCheckout"])->middleware(PreventDuplicatePayment::class);
    Route::post("/domain/checkout", [DomainResellerController::class, "submitCheckout"]);

    Route::get("/domain/renew/{id}", [DomainResellerController::class, "renewPage"])->name('renew');
    Route::post("/domain/renew/{id}", [DomainResellerController::class, "renewCheckout"]);

    Route::get("/domain-list/activate", [DomainResellerController::class, "activateCustomDomain"])->name("list.domain.activate");

    Route::get("/countries", [DomainResellerController::class, "getCountries"])->name("countries");
    Route::get("/states", [DomainResellerController::class, "getStates"])->where('countryKey', '[A-Z]{2}')->name("states");

    Route::get('/order-success/{id}',[PaymentLogController::class, 'order_payment_success'])->name('payment.success');
    Route::get('/order-cancel/{id}',[PaymentLogController::class, 'order_payment_cancel'])->name('payment.cancel');
    Route::get('/order-cancel-static',[PaymentLogController::class, 'order_payment_cancel_static'])->name('payment.cancel.static');
    Route::get('/order-confirm/{id}',[PaymentLogController::class, 'order_confirm'])->name('payment.confirm');
});


Route::middleware(['auth:admin','adminglobalVariable', 'setlang'])
    ->prefix('admin-home/landlord/domain-reseller')
    ->name('landlord.admin.domain-reseller.')
    ->group(function () {
        Route::get("/", [DomainResellerController::class, "index"])->name("index");
        Route::post("/search-domain", [DomainResellerController::class, "searchDomain"])->name("search.domain");

        Route::get("/domain-list", [DomainResellerController::class, "domainList"])->name("list.domain");
        Route::get("/domain-list/failed", [DomainResellerController::class, "failedDomainList"])->name("list.domain.failed");

        Route::get("/domain-list/failed/{id}/complete", [DomainResellerController::class, "failedPurchaseAction"])->name("list.domain.failed.complete");

        Route::get("/settings", [DomainResellerController::class, "settings"])->name("settings");
        Route::post("/settings/update", [DomainResellerController::class, "updateSettings"])->name("settings.update");
        Route::post("/configuration/update", [DomainResellerController::class, "updateConfiguration"])->name("configuration.update");

        Route::post("/additional-settings/update", [DomainResellerController::class, "updateAdditionalSettings"])->name("additional.settings.update");

        Route::get("/status", [DomainResellerController::class, "changeStatus"])->name("status.change");

        Route::get("assets/domain-reseller/logo/{logo}", function ($logo){
            return response()->file("core/Modules/DomainReseller/assets/logo/{$logo}");
        })->name("logo");
    });


Route::middleware(['maintenance_mode','landlord_glvar'])
    ->controller(PaymentLogController::class)
    ->prefix('domain-reseller')
    ->name('landlord.admin.domain-reseller.')
    ->group(function () {
    Route::match(['get','post'],'/ipn/{gateway}/{data?}', 'globalIpn')->name('global.ipn');
});
