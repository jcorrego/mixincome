<?php

declare(strict_types=1);

use App\Livewire\Management\CurrencyIndex;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(CurrencySeeder::class);
    $this->user = User::factory()->create();
});

// Test 12.2: Component renders with all currencies
test('component renders with all currencies', function (): void {
    Livewire::actingAs($this->user)
        ->test(CurrencyIndex::class)
        ->assertSee('USD')
        ->assertSee('EUR')
        ->assertSee('COP')
        ->assertSee('United States Dollar')
        ->assertSee('Euro')
        ->assertSee('Colombian Peso');
});
