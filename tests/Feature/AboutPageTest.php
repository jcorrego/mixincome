<?php

declare(strict_types=1);

test('about page returns successful response', function () {
    $response = $this->get('/about');

    $response->assertStatus(200);
});

test('about page displays expected content', function () {
    $response = $this->get('/about');

    $response->assertSee('About MixIncome');
    $response->assertSee('logo-color.svg');
    $response->assertSee('logo-white.svg');
});
