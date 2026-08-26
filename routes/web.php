<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Item;

Route::get('/', function () {
$items= Item::all();

    return Inertia::render('Welcome', [
        'items' => $items,
    ]);
});
Route::get('/about', function () {
    return Inertia::render('About');
});

