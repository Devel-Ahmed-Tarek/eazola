<?php


use Illuminate\Support\Facades\Route;

use Modules\Restaurant\Http\Controllers\Tenant\Backend\MenuCategoryController;
use Modules\Restaurant\Http\Controllers\Tenant\Backend\MenuSubCategoryController;
use Modules\Restaurant\Http\Controllers\Tenant\Backend\MenuAttributeController;
use Modules\Restaurant\Http\Controllers\Tenant\Backend\PickupAddressController;
use Modules\Restaurant\Http\Controllers\Tenant\Backend\MenuManageController;
use Modules\Restaurant\Http\Controllers\Tenant\Backend\TaxManageController;
use Modules\Restaurant\Http\Controllers\Tenant\Backend\MenuOrderController;
use Modules\Restaurant\Http\Controllers\Tenant\Frontend\RestaurantPaymentController;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;


Route::middleware([
    'web',
    \App\Http\Middleware\Tenant\InitializeTenancyByDomainCustomisedMiddleware::class,
    PreventAccessFromCentralDomains::class,
    'auth:admin',
    'tenant_admin_glvar',
    'package_expire',
    'tenantAdminPanelMailVerify',
    'setlang'
])->prefix('admin-home')->name('tenant.')->group(function () {
    Route::group(['as' => 'admin.'], function (){

        /*-----------------------------------
           MENU CATEGORY  ROUTES
       ------------------------------------*/
        Route::group(['prefix' => 'menu-category', 'as' => 'menu.category.'], function () {
            Route::controller(MenuCategoryController::class)->group(function ()
            {
                Route::get('/category', 'index')->name('all');
                Route::post('/new', 'store')->name('new');
                Route::post('/update', 'update')->name('update');
                Route::post('/deletee/{item}', 'destroy')->name('delete');
                Route::post('/bulk-action', 'bulk_action')->name('bulk.action');
                Route::post("sub-category","getSubCategory")->name("sub-category");

                Route::prefix('trash')->name('trash.')->group(function () {
                    Route::get('/', 'trash')->name('all');
                    Route::get('/restore/{id}', 'trash_restore')->name('restore');
                    Route::get('/delete/{id}', 'trash_delete')->name('category.delete');
                    Route::post('/bulk/delete', 'trash_bulk_delete')->name('delete.bulk');
                });
            });

        });


        /*-----------------------------------
          MENU CATEGORY  ROUTES
      ------------------------------------*/
        Route::group(['prefix' => 'menu-tax', 'as' => 'menu.tax.'], function () {
            Route::controller(TaxManageController::class)->group(function ()
            {
                Route::get('/', 'index')->name('all');
                Route::post('/new', 'store')->name('new');
                Route::post('/update', 'update')->name('update');
                Route::post('/delete/{item}', 'destroy')->name('delete');
                Route::post('/bulk-action', 'bulk_action')->name('bulk.action');

            });

        });

        /*-----------------------------------
           Menu SUB-CATEGORY  ROUTES
       ------------------------------------*/
        Route::group(['prefix' => 'sub-categories', 'as' => 'menu.subcategory.'], function () {
            Route::controller(MenuSubCategoryController::class)->group(function ()
            {
                Route::get('/all', 'index')->name('all');
                Route::post('/add-new', 'store')->name('new');
                Route::post('/single-update', 'update')->name('single.update');
                Route::post('/single-delete/{item}', 'destroy')->name('single.delete');
                Route::post('/bulk-delete', 'bulk_action')->name('bulk.delete.all');

                Route::prefix('trash')->name('trash.')->group(function () {
                    Route::get('/all', 'trash')->name('all');
                    Route::get('/restore/{id}', 'trash_restore')->name('restore');
                    Route::get('/single-delete/{id}', 'trash_delete')->name('single.delete');
                    Route::post('/bulk/delete/all', 'trash_bulk_delete')->name('bulk.delete.all');
                });

            });

        });


        /*-----------------------------------
        MENU Order  ROUTES
    ------------------------------------*/
        Route::group(['prefix' => 'menu-order', 'as' => 'menu.order.'], function () {
            Route::controller(MenuOrderController::class)->group(function ()
            {
                Route::get('/', 'index')->name('all');
                Route::get('/approved', 'approved_order')->name('approved');
                Route::get("/cancel-requested", 'cancel_requested_order')->name("cancel-requested");
                Route::get('/canceled', 'canceled_order')->name('canceled');
                Route::post('/order/status/update','order_status_update')->name('status-update');
                Route::get('/reports', 'order_report')->name('report');
                Route::post('/report-search', 'report_search')->name('report.search');
            });

        });


        /*-----------------------------------
           MENU MENU-ATTRIBUTE  ROUTES
       ------------------------------------*/

        Route::group(['prefix' => 'menu-attributes', 'as' => 'menu.attributes.'], function () {
            Route::controller(MenuAttributeController::class)->group(function (){
                Route::get('/', 'index')->name('all');
                Route::get('/new', 'create')->name('store');
                Route::post('/new', 'store');
                Route::get('/edit/{item}', 'edit')->name('edit');
                Route::post('/update', 'update')->name('update');
                Route::post('/delete/{item}', 'destroy')->name('delete');
                Route::post('/bulk-action', 'bulk_action')->name('bulk.action');
            });
        });

        /*-----------------------------------
          Pickup Address  ROUTES
       ------------------------------------*/
        Route::group(['prefix' => 'pickup-address', 'as' => 'menu.pickup.'], function () {
            Route::controller(PickupAddressController::class)->group(function (){
                Route::get('/', 'pickup_address')->name('address');
                Route::post('/', 'update_pickup_address');
            });
        });

        /*==============================================
                    Menu MODULE
    ==============================================*/
        Route::prefix('menu')->as("menu.manage.")->group(function ()
        {
            Route::controller(MenuManageController::class)->group(function (){
                Route::get("all",'index')->name("all");
                Route::get("create","create")->name("create");
                Route::post("create","store");
                Route::post("status-update","update_status")->name("update.status");
                Route::get("update/{id}/{aria_name?}", "edit")->name("edit");
                Route::post("update/{id}", "update");
                Route::post("destroy/{id}", "destroy")->name("destroy");
                Route::get("clone/{id}", "clone")->name("clone");
                Route::post("bulk/destroy", "bulk_destroy")->name("bulk.destroy");
                Route::get("search","productSearch")->name("search");
                Route::post('/orderable/status/update','orderable_status_updated')->name('orderable_status_updated');
            });
        });

    });

});

Route::middleware([
    'web',
    \App\Http\Middleware\Tenant\InitializeTenancyByDomainCustomisedMiddleware::class,
    PreventAccessFromCentralDomains::class,
    'tenant_glvar',
    'setlang'
])->group(function () {

    /*----------------------------------------------------------------------------------------------------------------------------
    |                                                      FRONTEND ROUTES (Tenants)
    |----------------------------------------------------------------------------------------------------------------------------*/

    Route::middleware('package_expire')->controller(
        \Modules\Restaurant\Http\Controllers\Tenant\Frontend\FrontendController::class)
        ->prefix('menu')->name('tenant.frontend.menu.')->group(function () {
            Route::get('/details/{slug}', 'menuDetails')->name('details');
    });


    /*----------------------------------------------------------------------------------------------------------------------------
   |                                                      Checkout ROUTES (Tenants)
   |----------------------------------------------------------------------------------------------------------------------------*/

    Route::middleware('package_expire')->controller(
        \Modules\Restaurant\Http\Controllers\Tenant\Frontend\CheckoutController::class)
        ->prefix('checkout/menu')->name('tenant.frontend.menu.')->group(function () {
            Route::post('/add-to-cart', 'addToCart')->name('add_to_cart');
            Route::post('/remove/cart', 'cartRemove')->name('cart.remove');
            Route::post('/price-calculate', 'menuPriceCalculate')->name('price-calculate');
            Route::post('/cart-quantity/increment', 'cartQuantityIncrement')->name('cart.quantity.increment');
            Route::post('/cart-quantity/decrement', 'cartQuantityDecrement')->name('cart.quantity.decrement');
            Route::post('/checkout/price-calculate', 'menuCheckoutPriceCalculate')->name('checkout.price-calculate');

            Route::post('/cart-quantity/update', 'cartQuantityUpdate')->name('cart.quantity.update');
            Route::post('/content', 'menuContent')->name('content');
            Route::any('/confirmation', 'confirmation')->name('confirmation');
        });

    // restaurant Payment
Route::middleware('package_expire')->controller(RestaurantPaymentController::class)->prefix('restaurant-payment')->name('tenant.')->group(function () {
        Route::post('/store', 'order_store')->name('frontend.restaurant.payment.store');
    });

    Route::middleware('package_expire')->controller(RestaurantPaymentController::class)->prefix('restaurant-payment')->name('tenant.')->group(function () {
        Route::get('/payment/success/{id}', 'restaurant_payment_success')->name('frontend.restaurant.payment.success');
        Route::get('/static/payment/cancel', 'restaurant_payment_cancel')->name('frontend.restaurant.payment.cancel');
        Route::post('/store', 'order_store')->name('frontend.restaurant.payment.store');
        Route::post('/paypal-ipn', 'paypal_ipn')->name('frontend.restaurant.paypal.ipn');
        Route::post('/paytm-ipn', 'paytm_ipn')->name('frontend.restaurant.paytm.ipn');
        Route::get('/mollie-ipn', 'mollie_ipn')->name('frontend.restaurant.mollie.ipn');
        Route::get('/stripe-ipn', 'stripe_ipn')->name('frontend.restaurant.stripe.ipn');
        Route::post('/razorpay-ipn', 'razorpay_ipn')->name('frontend.restaurant.razorpay.ipn');
        Route::post('/payfast-ipn', 'payfast_ipn')->name('frontend.restaurant.payfast.ipn');
        Route::get('/flutterwave/ipn', 'flutterwave_ipn')->name('frontend.restaurant.flutterwave.ipn');
        Route::get('/paystack-ipn', 'paystack_ipn')->name('frontend.restaurant.paystack.ipn');
        Route::get('/midtrans-ipn', 'midtrans_ipn')->name('frontend.restaurant.midtrans.ipn');
        Route::get('/cashfree-ipn', 'cashfree_ipn')->name('frontend.restaurant.cashfree.ipn');
        Route::get('/instamojo-ipn', 'instamojo_ipn')->name('frontend.restaurant.instamojo.ipn');
        Route::get('/paypal-ipn', 'paypal_ipn')->name('frontend.paypal.restaurant.ipn');
        Route::get('/marcadopago-ipn', 'marcadopago_ipn')->name('frontend.restaurant.marcadopago.ipn');
        Route::get('/squareup-ipn', 'squareup_ipn')->name('frontend.restaurant.squareup.ipn');
        Route::post('/cinetpay-ipn', 'cinetpay_ipn')->name('frontend.restaurant.cinetpay.ipn');
        Route::post('/paytabs-ipn', 'paytabs_ipn')->name('frontend.restaurant.paytabs.ipn');
        Route::post('/billplz-ipn', 'billplz_ipn')->name('frontend.restaurant.billplz.ipn');
        Route::post('/zitopay-ipn', 'zitopay_ipn')->name('frontend.restaurant.zitopay.ipn');
        Route::post('/toyyibpay-ipn', 'toyyibpay_ipn')->name('frontend.restaurant.toyyibpay.ipn');
        Route::post('/pagali-ipn', 'pagali_ipn')->name('frontend.restaurant.pagali.ipn');
        Route::get('/authorizenet-ipn', 'authorizenet_ipn')->name('frontend.restaurant.authorizenet.ipn');
        Route::get('/sitesway-ipn', 'sitesway_ipn')->name('frontend.restaurant.sitesway.ipn');
        Route::post('/kinetic-ipn', 'kinetic_ipn')->name('frontend.restaurant.kinetic.ipn')->excludedMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    });

    //menu background image rendered
    Route::get("assets/img/main/{bg_image}", function ($bg_image){
        return response()->file("core/Modules/Restaurant/assets/img/main/{$bg_image}");
    })->name("bg_image");

});
