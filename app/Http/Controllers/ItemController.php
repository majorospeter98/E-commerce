<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('items/Index', [
            'items' => Item::all(),
        ]);
    }

    public function show(Item $item): Response
    {
        $favourites = Auth::user() ? Auth::user()->favourites()->get() : [];

        return Inertia::render('items/Show', [
            'item' => $item,
            'isFavourites' => $favourites,
            'prices' => Item::pluck('price', 'id'),
        ]);
    }
}
