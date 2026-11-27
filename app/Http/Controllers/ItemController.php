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

        $largePath = "Teams/large/{$item->team}/{$item->image}";

        return Inertia::render('items/Show', [
            'largeImage' => is_file(public_path($largePath)) ? "/{$largePath}" : null,
            'item' => $item,
            'isFavourites' => $favourites,
            'prices' => Item::pluck('price', 'id'),
        ]);
    }
}
