<?php

use App\Models\Favourite;
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
     $test= Auth::user() ? Auth::user()->favourites()->get() : [];
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
})->middleware('guest') ;
Route::post('/login', function () {
    $validated = request()->validate([
        'email' =>  ['required', 'min:8', 'email'],
        'password' => ['required', 'min:8']
    ]);
    if (Auth::attempt($validated)) {
        Inertia::flash('message', 'Sikeres bejelentkezés!');
return redirect('/')->with('Sikeres Bejeltnkezés');
    }
     Inertia::flash('message2', 'Sikertelen!');
return back()
    ->withErrors([
        'email' => 'A megadatott adatokkal nincs fiók a rendszerben',
    ])
    ->withInput();
})->middleware('guest') ->name('login');

//fav
Route::get('/favourites', function () {

$fav=Auth::user()->favourites()->with('item')->get();
    return Inertia::render('Favourites', [
        'fav' => $fav
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
        'name' =>  ['required', 'min:5'],
        'email' =>  ['required', 'min:8', 'email', 'unique:users,email'],
        'password' => ['required', 'min:8']
    ]);
    User::create([
        'name' => request('name'),
        'email' => request('email'),
        'password' => Hash::make(request('password'))
    ]);
return redirect('/login');
    
})->middleware('guest');
Route::delete('/account/logout', function () {
    Auth::logout();
})->middleware('auth');


Route::get('/cart', function () {
    $cartItems=request()->all();
    return Inertia::render('items/Cart',[
'cart' => $cartItems,
    ]);
});
Route::get('/account', function (){

if(!Auth::user()){
return redirect('/login');
}

return Inertia::render('account/Account');
});