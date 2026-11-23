<?php

use App\Http\Middleware\SetLocale;
use App\Models\Favourite;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

Route::post('/locale', function () {
    $validated = request()->validate([
        'locale' => ['required', Rule::in(SetLocale::SUPPORTED_LOCALES)],
    ]);

    Cookie::queue('locale', $validated['locale'], 60 * 24 * 365);

    return back();
});

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
        'email.required' => __('msg.email.required'),
        'email.email' => __('msg.email.email'),
        'email.min' => __('msg.email.min'),
        'password.required' => __('msg.password.required'),
        'password.min' => __('msg.password.min'),
    ]);
    if (Auth::attempt($validated)) {
        Inertia::flash('message', __('msg.login.success'));

        return redirect('/')->with('Sikeres Bejeltnkezés');
    }
    Inertia::flash('message2', __('msg.login.failed'));

    return back()
        ->withErrors([
            'email' => __('msg.login.invalid'),
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
        'name.required' => __('msg.name.required'),
        'name.min' => __('msg.name.min'),
        'email.required' => __('msg.email.required'),
        'email.email' => __('msg.email.email'),
        'email.min' => __('msg.email.min'),
        'email.unique' => __('msg.email.unique'),
        'password.required' => __('msg.password.required'),
        'password.min' => __('msg.password.min'),
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

    return Inertia::render('account/Account', [
        'name' => Auth::user()->name,
        'orderCount' => Auth::user()->orders()->count(),
        'favouriteCount' => Auth::user()->favourites()->count(),
    ]);
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

    return redirect('/account/order');
})->middleware('auth');
