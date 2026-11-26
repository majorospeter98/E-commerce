<?php

test('redirects stay on http for a direct http request', function () {
    $this->get('http://localhost/account/order')
        ->assertRedirect('http://localhost/login');
});

test('redirects use https behind a proxy that terminates TLS', function () {
    $this->get('http://localhost/account/order', ['X-Forwarded-Proto' => 'https'])
        ->assertRedirect('https://localhost/login');
});

test('a forwarded host header does not change where redirects point', function () {
    $this->get('http://localhost/account/order', ['X-Forwarded-Host' => 'evil.example'])
        ->assertRedirect('http://localhost/login');
});
