<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


Route::get('/items', function () {
    $items = Item::all();

    return Inertia::render('items/Index', [
        'items' => $items,
    ]);
});
Route::get('/items/{item}', function (Item $item) {
    return Inertia::render('items/Show', [
        'item' => $item,
    ]);
});


Route::get('/', function () {
    return redirect('/items');
});
Route::get('/login', function () {
    return Inertia::render('Login');
});
Route::post('/login', function () {
    $validated = request()->validate([
        'email' =>  ['required', 'min:8', 'email'],
        'password' => ['required', 'min:8']
    ]);
    if (Auth::attempt($validated)) {
        dd("Van ilyen");
    }
});
Route::get('/register', function () {
    return Inertia::render('Register');
});
Route::post('/register', function () {
    request()->validate([
        'name' =>  ['required', 'min:8'],
        'email' =>  ['required', 'min:8', 'email'],
        'password' => ['required', 'min:8']
    ]);
    User::create([
        'name' => request('name'),
        'email' => request('email'),
        'password' => Hash::make(request('password'))
    ]);

    return redirect('/');
});
