<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('a missing page or product shows the not found page', function (string $url) {
    $this->get($url)
        ->assertNotFound()
        ->assertInertia(fn (Assert $page) => $page->component('NotFound'));
})->with(['/items/999', '/no-such-page']);
