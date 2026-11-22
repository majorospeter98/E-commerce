<?php

use App\Models\Favourite;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/items', function () {
    $items = Item::all();

    return Inertia::render('items/Index', [
        'items' => $items,
    ]);
});
Route::get('/items/{item}', function (Item $item) {
    $test = Auth::user() ? Auth::user()->favourites()->get() : [];

    return Inertia::render('items/Show', [
        'item' => $item,
        'isFavourites' => $test,
    ]);
});

Route::get('/', function () {
    return redirect('/items');
});
Route::get('/login', function () {
    return Inertia::render('account/Login');
})->middleware('guest');
Route::post('/login', function () {
    $validated = request()->validate([
        'email' => ['required', 'min:8', 'email'],
        'password' => ['required', 'min:8'],
    ], [
        'email.required' => 'Add meg az email címedet.',
        'email.email' => 'Ez nem érvényes email cím. Példa: nev@pelda.hu',
        'email.min' => 'Az email cím legalább 8 karakter legyen.',
        'password.required' => 'Add meg a jelszavadat.',
        'password.min' => 'A jelszó legalább 8 karakter legyen.',
    ]);
    if (Auth::attempt($validated)) {
        Inertia::flash('message', 'Sikeres bejelentkezés!');

        return redirect('/')->with('Sikeres Bejeltnkezés');
    }
    Inertia::flash('message2', 'Sikertelen!');

    return back()
        ->withErrors([
            'email' => 'Hibás email cím vagy jelszó. Ellenőrizd az adatokat, vagy regisztrálj új fiókot.',
        ])
        ->withInput();
})->middleware('guest')->name('login');

// fav
Route::get('/account/favourites', function () {

    $fav = Auth::user()->favourites()->with('item')->get();

    return Inertia::render('account/Favourites', [
        'fav' => $fav,
    ]);
})->middleware('auth');
Route::post('/toFavourites', function () {

    Favourite::create([
        'user_id' => Auth::id(),
        'item_id' => request('item_id'),
    ]);
})->middleware('auth');

Route::delete('/deleteFavourite', function () {

    Favourite::where([
        'user_id' => Auth::id(),
        'item_id' => request('item_id'),
    ])->delete();
})->middleware('auth');

Route::get('/register', function () {
    return Inertia::render('account/Register');
})->middleware('guest');

Route::post('/register', function () {
    request()->validate([
        'name' => ['required', 'min:5'],
        'email' => ['required', 'min:8', 'email', 'unique:users,email'],
        'password' => ['required', 'min:8'],
    ], [
        'name.required' => 'Add meg a neved.',
        'name.min' => 'A név legalább 5 karakter legyen.',
        'email.required' => 'Add meg az email címedet.',
        'email.email' => 'Ez nem érvényes email cím. Példa: nev@pelda.hu',
        'email.min' => 'Az email cím legalább 8 karakter legyen.',
        'email.unique' => 'Ezzel az email címmel már van fiók. Jelentkezz be.',
        'password.required' => 'Add meg a jelszavadat.',
        'password.min' => 'A jelszó legalább 8 karakter legyen.',
    ]);
    User::create([
        'name' => request('name'),
        'email' => request('email'),
        'password' => Hash::make(request('password')),
    ]);

    return redirect('/login');
})->middleware('guest');
Route::delete('/account/logout', function () {
    Auth::logout();
})->middleware('auth');

Route::get('/cart', function () {
    $cartItems = request()->all();

    return Inertia::render('items/Cart', [
        'cart' => $cartItems,
    ]);
});
Route::get('/account', function () {

    if (! Auth::user()) {
        return redirect('/login');
    }

    return Inertia::render('account/Account');
});
Route::get('/account/order', function () {
    $orders = Auth::user()->orders()->with('items')->get();

    return Inertia::render('account/Order', [
        'orders' => $orders,
    ]);
})->middleware('auth');
Route::post('/account/order', function () {

    $orderstoDB = request('orders');

    $order = Order::create([

        'user_id' => Auth::id(),

    ]);
    foreach ($orderstoDB as $item) {

        OrderItem::create([

            'item_id' => $item['item_id'],
            'order_id' => $order->id,
            'size' => $item['size'],
            'quantity' => $item['quantity'],

        ]);
    }
});
