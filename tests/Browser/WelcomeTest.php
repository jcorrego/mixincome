<?php

declare(strict_types=1);

it('has login page as home', function (): void {
    $page = visit('/');

    $page->assertSee('Log in');
});
