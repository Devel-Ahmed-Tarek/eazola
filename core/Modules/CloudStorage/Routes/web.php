<?php

use Modules\CloudStorage\Http\Controllers\CloudStorageController;

Route::middleware(['auth:admin', 'adminglobalVariable'])
    ->prefix('admin-home/cloud-storage/')
    ->name('landlord.')
    ->controller(CloudStorageController::class)
    ->group(function () {
        Route::get('/', 'index')->name('admin.cloud.storage.settings');
        Route::post('/', 'update_storage');


    });
