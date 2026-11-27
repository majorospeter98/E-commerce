<?php

use App\Models\Item;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('a guest can place an order without an account', function () {
    $item = Item::create(['team' => 'Arsenal', 'type' => 'Home', 'brand' => 'Adidas', 'image' => 'a.png', 'quantity' => '5', 'size' => 'M']);

    $this->post('/account/order', ['orders' => [['item_id' => $item->id, 'size' => 'M', 'quantity' => 2]]])
        ->assertRedirect('/cart');

    $this->assertDatabaseHas('orders', ['user_id' => null]);
    $this->assertDatabaseHas('order_items', ['item_id' => $item->id, 'quantity' => 2]);
});

test('guests cannot view the order history', function () {
    $this->get('/account/order')->assertRedirect('/login');
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

test('the price paid comes from the catalogue and survives later price changes', function () {
    $item = Item::create(['team' => 'Arsenal', 'type' => 'Home', 'brand' => 'Adidas', 'image' => 'a.png', 'quantity' => '5', 'size' => 'M', 'price' => 29990]);

    $this->post('/account/order', ['orders' => [['item_id' => $item->id, 'size' => 'M', 'quantity' => 2, 'unit_price' => 1]]])
        ->assertRedirect('/cart');
    $item->update(['price' => 35000]);

    expect(OrderItem::sole()->unit_price)->toEqual(29990);
});

test('the order history lists the newest order first with the price paid', function () {
    $user = User::factory()->create();
    $item = Item::create(['team' => 'Arsenal', 'type' => 'Home', 'brand' => 'Adidas', 'image' => 'a.png', 'quantity' => '5', 'size' => 'M', 'price' => 29990]);
    $older = $user->orders()->create();
    $newer = $user->orders()->create();
    $newer->items()->attach($item, ['size' => 'M', 'quantity' => 1, 'unit_price' => 29990]);

    $this->actingAs($user)
        ->get('/account/order')
        ->assertInertia(fn (Assert $page) => $page
            ->where('orders.0.id', $newer->id)
            ->where('orders.0.items.0.pivot.unit_price', 29990)
            ->where('orders.1.id', $older->id));
});

test('the cart and product pages receive the current prices', function () {
    $item = Item::create(['team' => 'Arsenal', 'type' => 'Home', 'brand' => 'Adidas', 'image' => 'a.png', 'quantity' => '5', 'size' => ['M'], 'price' => 29990]);

    foreach (['/cart', "/items/{$item->id}"] as $url) {
        $this->get($url)->assertInertia(fn (Assert $page) => $page->where("prices.{$item->id}", 29990));
    }
});
