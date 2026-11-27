<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('items/Cart', [
            'prices' => Item::pluck('price', 'id'),
        ]);
    }
}
