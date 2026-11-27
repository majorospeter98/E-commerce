<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'team' => 'Barcelona',
                'type' => 'Home',
                'brand' => 'Nike',
                'quantity' => 130,
                'size' => ['S', 'L', 'XL'],
                'image' => 'home.webp',
                'price' => 29990,
            ],
            [
                'team' => 'Barcelona',
                'type' => 'Away',
                'brand' => 'Nike',
                'quantity' => 100,
                'size' => ['S', 'L', 'XL'],
                'image' => 'away.webp',
                'price' => 29990,
            ],
            [
                'team' => 'Barcelona',
                'type' => 'Home',
                'brand' => 'Nike',
                'quantity' => 40,
                'size' => ['S', 'L', 'XL'],
                'image' => 'short.webp',
                'price' => 12990,
            ],
            [
                'team' => 'Real Madrid',
                'type' => 'Home',
                'brand' => 'Adidas',
                'quantity' => 150,
                'size' => ['S', 'L', 'XL'],
                'image' => 'home.webp',
                'price' => 29990,
            ],
            [
                'team' => 'Real Madrid',
                'type' => 'Away',
                'brand' => 'Adidas',
                'quantity' => 120,
                'size' => ['S', 'L', 'XL'],
                'image' => 'away.webp',
                'price' => 29990,
            ],
            [
                'team' => 'Real Madrid',
                'type' => 'Home',
                'brand' => 'Adidas',
                'quantity' => 40,
                'size' => ['S', 'L', 'XL'],
                'image' => 'short.webp',
                'price' => 12990,
            ],
        ];

        foreach ($items as $item) {
            Item::firstOrCreate(
                ['team' => $item['team'], 'image' => $item['image']],
                $item,
            );
        }
    }
}
