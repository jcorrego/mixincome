<?php

declare(strict_types=1);

use App\Livewire\Management\CurrencyShow;
use App\Models\Currency;
use App\Models\FxRate;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(CurrencySeeder::class);
    $this->user = User::factory()->create();
    $this->eur = Currency::query()->where('code', 'EUR')->firstOrFail();
    $this->usd = Currency::query()->where('code', 'USD')->firstOrFail();
    $this->cop = Currency::query()->where('code', 'COP')->firstOrFail();
});

// Test 12.4: Component renders with currency metadata and rates
test('component renders with currency metadata and rates', function (): void {
    FxRate::factory()->create([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'date' => Date::parse('2024-06-14'),
        'rate' => '1.08000000',
    ]);

    Livewire::actingAs($this->user)
        ->test(CurrencyShow::class, ['currency' => $this->eur])
        ->assertSee('EUR')
        ->assertSee('Euro')
        ->assertSee('1.08');
});

// Test 12.5: fetchRate action with valid data
test('fetchRate creates new rate successfully', function (): void {
    Http::fake([
        '*' => Http::response(file_get_contents(__DIR__.'/../../../Fixtures/ecb_usd_eur_2024-06-14.xml'), 200),
    ]);

    Livewire::actingAs($this->user)
        ->test(CurrencyShow::class, ['currency' => $this->eur])
        ->set('fromCurrencyId', $this->eur->id)
        ->set('toCurrencyId', $this->usd->id)
        ->set('date', '2024-06-14')
        ->call('fetchRate')
        ->assertHasNoErrors();

    expect(FxRate::query()->count())->toBe(1);
});

// Test 12.6: fetchRate with duplicate rate
test('fetchRate shows error for duplicate rate', function (): void {
    FxRate::factory()->create([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'date' => Date::parse('2024-06-14'),
        'rate' => '1.08000000',
    ]);

    Livewire::actingAs($this->user)
        ->test(CurrencyShow::class, ['currency' => $this->eur])
        ->set('fromCurrencyId', $this->eur->id)
        ->set('toCurrencyId', $this->usd->id)
        ->set('date', '2024-06-14')
        ->call('fetchRate')
        ->assertHasErrors(['fetchRate']);
});

// Test 12.7: fetchRate with future date
test('fetchRate validates future date', function (): void {
    $tomorrow = Date::tomorrow()->toDateString();

    Livewire::actingAs($this->user)
        ->test(CurrencyShow::class, ['currency' => $this->eur])
        ->set('fromCurrencyId', $this->eur->id)
        ->set('toCurrencyId', $this->usd->id)
        ->set('date', $tomorrow)
        ->call('fetchRate')
        ->assertHasErrors(['date']);
});

// Test 12.8: fetchRate with same currency
test('fetchRate validates different currencies', function (): void {
    Livewire::actingAs($this->user)
        ->test(CurrencyShow::class, ['currency' => $this->eur])
        ->set('fromCurrencyId', $this->eur->id)
        ->set('toCurrencyId', $this->eur->id)
        ->set('date', '2024-06-14')
        ->call('fetchRate')
        ->assertHasErrors(['toCurrencyId']);
});

// Test 12.9: fetchRate with ECB failure
test('fetchRate handles ECB failure gracefully', function (): void {
    Http::fake([
        '*' => Http::response('', 500),
    ]);

    Livewire::actingAs($this->user)
        ->test(CurrencyShow::class, ['currency' => $this->eur])
        ->set('fromCurrencyId', $this->eur->id)
        ->set('toCurrencyId', $this->usd->id)
        ->set('date', '2024-06-14')
        ->call('fetchRate')
        ->assertHasErrors(['fetchRate']);
});

// Test 12.10: refetchRate with different ECB value
test('refetchRate updates rate successfully', function (): void {
    $existingRate = FxRate::factory()->create([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'date' => Date::parse('2024-06-14'),
        'rate' => '1.08000000',
    ]);

    Http::fake([
        '*' => Http::response(file_get_contents(__DIR__.'/../../../Fixtures/ecb_usd_eur_2024-06-14.xml'), 200),
    ]);

    Livewire::actingAs($this->user)
        ->test(CurrencyShow::class, ['currency' => $this->eur])
        ->call('refetchRate', $existingRate->id)
        ->assertDispatched('rate-refetched');

    $existingRate->refresh();
    expect($existingRate->updated_at)->not->toBeNull();
});

// Test 12.11: refetchRate with same ECB value
test('refetchRate handles unchanged rate value', function (): void {
    $existingRate = FxRate::factory()->create([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'date' => Date::parse('2024-06-14'),
        'rate' => '0.92640000',
    ]);

    Http::fake([
        '*' => Http::response(file_get_contents(__DIR__.'/../../../Fixtures/ecb_usd_eur_2024-06-14.xml'), 200),
    ]);

    Livewire::actingAs($this->user)
        ->test(CurrencyShow::class, ['currency' => $this->eur])
        ->call('refetchRate', $existingRate->id)
        ->assertDispatched('rate-refetched');
});

// Test 12.12: refetchRate updating replicated rate
test('refetchRate updates replicated rate to ECB-sourced', function (): void {
    $replicatedRate = FxRate::factory()->create([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'date' => Date::parse('2024-06-14'),
        'rate' => '1.08000000',
        'is_replicated' => true,
        'replicated_from_date' => Date::parse('2024-06-13'),
    ]);

    Http::fake([
        '*' => Http::response(file_get_contents(__DIR__.'/../../../Fixtures/ecb_usd_eur_2024-06-14.xml'), 200),
    ]);

    Livewire::actingAs($this->user)
        ->test(CurrencyShow::class, ['currency' => $this->eur])
        ->call('refetchRate', $replicatedRate->id)
        ->assertDispatched('rate-refetched');

    $replicatedRate->refresh();
    expect($replicatedRate->is_replicated)->toBeFalse();
});

// Test 12.13: refetchRate handles API failure
test('refetchRate handles API failure gracefully', function (): void {
    $existingRate = FxRate::factory()->create([
        'from_currency_id' => $this->eur->id,
        'to_currency_id' => $this->usd->id,
        'date' => Date::parse('2024-06-14'),
        'rate' => '1.08000000',
    ]);

    Http::fake([
        '*' => Http::response('', 500),
    ]);

    Livewire::actingAs($this->user)
        ->test(CurrencyShow::class, ['currency' => $this->eur])
        ->call('refetchRate', $existingRate->id)
        ->assertHasErrors(['refetchRate']);
});
