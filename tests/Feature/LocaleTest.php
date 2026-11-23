<?php

use Inertia\Testing\AssertableInertia;

it('defaults to Hungarian', function () {
    $this->get('/login')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('locale', 'hu')
            ->where('translations', fn ($strings) => $strings['login.submit'] === 'Bejelentkezés'));
});

it('shares the English strings when the locale cookie is en', function () {
    $this->withCookie('locale', 'en')
        ->get('/login')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('locale', 'en')
            ->where('translations', fn ($strings) => $strings['login.submit'] === 'Log in'));
});

it('falls back to Hungarian for an unsupported locale cookie', function () {
    $this->withCookie('locale', 'de')
        ->get('/login')
        ->assertInertia(fn (AssertableInertia $page) => $page->where('locale', 'hu'));
});

it('stores the chosen locale in a cookie', function () {
    $this->post('/locale', ['locale' => 'en'])
        ->assertRedirect()
        ->assertCookie('locale', 'en');
});

it('rejects an unsupported locale', function () {
    $this->post('/locale', ['locale' => 'de'])
        ->assertSessionHasErrors('locale')
        ->assertCookieMissing('locale');
});

it('translates validation messages with the chosen locale', function () {
    $this->post('/login', [])
        ->assertSessionHasErrors(['email' => 'Add meg az email címedet.']);

    $this->withCookie('locale', 'en')
        ->post('/login', [])
        ->assertSessionHasErrors(['email' => 'Enter your email address.']);
});
