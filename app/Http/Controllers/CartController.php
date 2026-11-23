<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('items/Cart');
    }
}
