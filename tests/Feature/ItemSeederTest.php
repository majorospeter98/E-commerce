<?php

use App\Models\Item;
use Database\Seeders\ItemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('seeding the catalogue twice does not duplicate items', function () {
    $this->seed(ItemSeeder::class);
    $this->seed(ItemSeeder::class);

    expect(Item::count())->toBe(6);
});

test('seeded shirts and shorts have their prices', function () {
    $this->seed(ItemSeeder::class);

    expect(Item::where('image', 'short.webp')->pluck('price')->unique()->all())->toBe([12990])
        ->and(Item::where('image', '!=', 'short.webp')->pluck('price')->unique()->all())->toBe([29990]);
});
