<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/items');

Route::post('/locale', [LocaleController::class, 'update']);

Route::get('/items', [ItemController::class, 'index']);
Route::get('/items/{item}', [ItemController::class, 'show']);
Route::get('/cart', [CartController::class, 'show']);

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin']);
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegister']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::delete('/account/logout', [AuthController::class, 'logout']);

    Route::get('/account', [AccountController::class, 'show']);

    Route::get('/account/favourites', [FavouriteController::class, 'index']);
    Route::post('/toFavourites', [FavouriteController::class, 'store']);
    Route::delete('/deleteFavourite', [FavouriteController::class, 'destroy']);

    Route::get('/account/order', [OrderController::class, 'index']);
    Route::post('/account/order', [OrderController::class, 'store']);
});
