<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFavouriteRequest;
use App\Models\Favourite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class FavouriteController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('account/Favourites', [
            'fav' => Auth::user()->favourites()->with('item')->get(),
        ]);
    }

    public function store(StoreFavouriteRequest $request): void
    {
        Favourite::firstOrCreate([
            'user_id' => Auth::id(),
            'item_id' => $request->validated('item_id'),
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Favourite::where([
            'user_id' => Auth::id(),
            'item_id' => $request->input('item_id'),
        ])->delete();

        return back();
    }
}
