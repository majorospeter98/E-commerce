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
