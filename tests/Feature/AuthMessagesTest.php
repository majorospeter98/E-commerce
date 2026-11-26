<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Support\SessionKey;

uses(RefreshDatabase::class);

test('registering confirms that the account was created', function () {
    $this->post('/register', ['name' => 'Test User', 'email' => 'new@example.com', 'password' => 'password123'])
        ->assertRedirect('/login')
        ->assertSessionHas(SessionKey::FLASH_DATA, ['message' => __('msg.register.success')]);
});

test('a failed login reports one field error and no banner', function () {
    $user = User::factory()->create();

    $this->from('/login')
        ->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertRedirect('/login')
        ->assertSessionHasErrors('email')
        ->assertSessionMissing(SessionKey::FLASH_DATA);
});
