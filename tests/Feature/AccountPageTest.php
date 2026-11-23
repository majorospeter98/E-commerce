<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('guests are redirected to login', function () {
    $this->get('/account')->assertRedirect('/login');
});

test('account page receives the user name and counts', function () {
    $user = User::factory()->create();
    Order::create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get('/account')
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/Account')
            ->where('name', $user->name)
            ->where('orderCount', 1)
            ->where('favouriteCount', 0));
});
