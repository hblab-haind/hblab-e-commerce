<?php

use Illuminate\Support\Facades\Route;
use Webkul\Wishlist\Http\Controllers\WishlistController;

Route::group(['prefix' => 'wishlist'], function () {
    Route::get('/search', [WishlistController::class, 'search']);
    Route::post('/store', [WishlistController::class, 'store']);
    Route::post('/destroy', [WishlistController::class, 'destroy']);
    Route::post('/bulk', [WishlistController::class, 'bulkAction']);
    Route::get('/export/{format}', [WishlistController::class, 'export']);
    Route::post('/admin-login', [WishlistController::class, 'adminLogin']);
});
