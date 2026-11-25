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

test('an invalid order payload is rejected and stores nothing', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/account/order', ['orders' => [['item_id' => 999, 'size' => 'M', 'quantity' => 0]]])
        ->assertSessionHasErrors(['orders.0.item_id', 'orders.0.quantity']);

    expect($user->orders()->count())->toBe(0);
});

test('favouriting the same item twice does not duplicate it', function () {
    $user = User::factory()->create();
    $item = Item::create(['team' => 'Arsenal', 'type' => 'Hazai', 'brand' => 'Adidas', 'image' => 'a.png', 'quantity' => '5', 'size' => 'M']);

    $this->actingAs($user)->post('/toFavourites', ['item_id' => $item->id]);
    $this->actingAs($user)->post('/toFavourites', ['item_id' => $item->id]);

    expect($user->favourites()->count())->toBe(1);
});

test('a favourite can be removed', function () {
    $user = User::factory()->create();
    $item = Item::create(['team' => 'Arsenal', 'type' => 'Hazai', 'brand' => 'Adidas', 'image' => 'a.png', 'quantity' => '5', 'size' => 'M']);
    $user->favourites()->create(['item_id' => $item->id]);

    $this->actingAs($user)->delete('/deleteFavourite', ['item_id' => $item->id])->assertRedirect();

    expect($user->favourites()->count())->toBe(0);
});
