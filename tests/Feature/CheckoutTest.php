<?php

use App\Models\Item;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot place an order', function () {
    $this->post('/account/order', ['orders' => []])->assertRedirect('/login');
});

test('placing an order stores its items and redirects to orders', function () {
    $user = User::factory()->create();
    $item = Item::create(['team' => 'Arsenal', 'type' => 'Home', 'brand' => 'Adidas', 'image' => 'a.png', 'quantity' => '5', 'size' => 'M']);

    $this->actingAs($user)
        ->post('/account/order', ['orders' => [['item_id' => $item->id, 'size' => 'M', 'quantity' => 2]]])
        ->assertRedirect('/account/order');

    expect($user->orders()->count())->toBe(1)
        ->and(OrderItem::first()->quantity)->toBe(2);
});
