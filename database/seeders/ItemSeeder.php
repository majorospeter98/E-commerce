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
            ],
            [
                'team' => 'Barcelona',
                'type' => 'Away',
                'brand' => 'Nike',
                'quantity' => 100,
                'size' => ['S', 'L', 'XL'],
                'image' => 'away.webp',
            ],
            [
                'team' => 'Barcelona',
                'type' => 'Home',
                'brand' => 'Nike',
                'quantity' => 40,
                'size' => ['S', 'L', 'XL'],
                'image' => 'short.webp',
            ],
            [
                'team' => 'Real Madrid',
                'type' => 'Home',
                'brand' => 'Adidas',
                'quantity' => 150,
                'size' => ['S', 'L', 'XL'],
                'image' => 'home.webp',
            ],
            [
                'team' => 'Real Madrid',
                'type' => 'Away',
                'brand' => 'Adidas',
                'quantity' => 120,
                'size' => ['S', 'L', 'XL'],
                'image' => 'away.webp',
            ],
            [
                'team' => 'Real Madrid',
                'type' => 'Home',
                'brand' => 'Adidas',
                'quantity' => 40,
                'size' => ['S', 'L', 'XL'],
                'image' => 'short.webp',
            ],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}