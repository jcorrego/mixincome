<?php

declare(strict_types=1);

use App\Models\Jurisdiction;
use Illuminate\Database\QueryException;

test('factory creates valid jurisdiction', function (): void {
    $jurisdiction = Jurisdiction::factory()->create();

    expect($jurisdiction)->toBeInstanceOf(Jurisdiction::class)
        ->and($jurisdiction->name)->toBeString()
        ->and($jurisdiction->iso_code)->toHaveLength(3)
        ->and($jurisdiction->timezone)->toBeString()
        ->and($jurisdiction->default_currency)->toHaveLength(3);
});

test('fillable attributes work correctly', function (): void {
    $jurisdiction = Jurisdiction::factory()->create()->refresh();

    expect(array_keys($jurisdiction->toArray()))
        ->toBe([
            'id',
            'name',
            'iso_code',
            'timezone',
            'default_currency',
            'created_at',
            'updated_at',
        ]);
});

test('database enforces unique constraint on iso_code', function (): void {
    Jurisdiction::factory()->create(['iso_code' => 'US']);

    Jurisdiction::factory()->create(['iso_code' => 'US']);
})->throws(QueryException::class);
