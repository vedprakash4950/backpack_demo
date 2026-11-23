<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('category', 'CategoryCrudController');
    Route::get('product/export/csv', [
        'as' => 'backpack.product.export.csv',
        'uses' => \App\Http\Controllers\Admin\ProductCrudController::class . '@exportCsv',
        'operation' => 'list',
    ]);
    Route::get('product/export/excel', [
        'as' => 'backpack.product.export.excel',
        'uses' => \App\Http\Controllers\Admin\ProductCrudController::class . '@exportExcel',
        'operation' => 'list',
    ]);
    Route::crud('product', 'ProductCrudController');
}); // this should be the absolute last line of this file

/**
 * DO NOT ADD ANYTHING HERE.
 */
