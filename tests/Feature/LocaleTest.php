<?php

test('validation messages are Hungarian unless the page asks otherwise', function (array $headers, string $message) {
    $this->post('/login', [], $headers)
        ->assertSessionHasErrors(['email' => $message]);
})->with([
    'no language sent' => [[], 'Add meg az email címedet.'],
    'English' => [['X-Locale' => 'en'], 'Enter your email address.'],
    'unsupported language' => [['X-Locale' => 'de'], 'Add meg az email címedet.'],
]);

test('no language cookie is set', function () {
    $this->get('/login', ['X-Locale' => 'en'])
        ->assertCookieMissing('locale');
});

test('a language cookie left from before is removed', function () {
    $this->withCookie('locale', 'en')
        ->get('/login')
        ->assertCookieExpired('locale');
});
