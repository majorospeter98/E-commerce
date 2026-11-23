<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function show(): Response
    {
        $user = Auth::user();

        return Inertia::render('account/Account', [
            'name' => $user->name,
            'orderCount' => $user->orders()->count(),
            'favouriteCount' => $user->favourites()->count(),
        ]);
    }
}
