<?php

test('the home page leads to the shop', function () {
    $this->get('/')->assertRedirect('/items');
});
