<?php

use App\Models\Item;
use Database\Seeders\ItemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('seeding the catalogue twice does not duplicate items', function () {
    $this->seed(ItemSeeder::class);
    $this->seed(ItemSeeder::class);

    expect(Item::count())->toBe(15);
});

test('seeded shirts and shorts have their prices', function () {
    $this->seed(ItemSeeder::class);

    expect(Item::where('image', 'short.webp')->pluck('price')->unique()->all())->toBe([12990])
        ->and(Item::where('image', '!=', 'short.webp')->pluck('price')->unique()->all())->toBe([29990]);
});

test('every seeded item has each image size the pages ask for', function () {
    $this->seed(ItemSeeder::class);

    $missing = Item::all()
        ->crossJoin([150, 360, 450])
        ->map(fn (array $pair) => "Teams/{$pair[1]}/{$pair[0]->team}/{$pair[0]->image}")
        ->reject(fn (string $path) => is_file(public_path($path)));

    expect($missing->values()->all())->toBe([]);
});
