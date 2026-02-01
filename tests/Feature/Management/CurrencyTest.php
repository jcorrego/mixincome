<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\FxRate;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->seed(CurrencySeeder::class);
    $this->user = User::factory()->create();
});

// Test 1.1: View currency index page
test('authenticated user can view currency index', function (): void {
    actingAs($this->user)
        ->get('/management/currencies')
        ->assertOk()
        ->assertSee('USD')
        ->assertSee('EUR')
        ->assertSee('COP');
});

// Test 1.2: Unauthenticated access denied
test('guest cannot access currency index', function (): void {
    get('/management/currencies')
        ->assertRedirect(route('login'));
});

// Test 2.1: View currency detail page
test('authenticated user can view currency detail', function (): void {
    $eur = Currency::query()->where('code', 'EUR')->firstOrFail();
    $usd = Currency::query()->where('code', 'USD')->firstOrFail();

    FxRate::factory()->create([
        'from_currency_id' => $eur->id,
        'to_currency_id' => $usd->id,
        'date' => Date::parse('2024-06-14'),
        'rate' => '1.08000000',
    ]);

    actingAs($this->user)
        ->get("/management/currencies/{$eur->code}")
        ->assertOk()
        ->assertSee('EUR')
        ->assertSee('Euro')
        ->assertSee('1.08');
});
