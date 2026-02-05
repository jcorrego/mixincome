<?php

declare(strict_types=1);

use App\Models\TransactionImport;
use App\Models\Entity;

test('transaction import can be created', function () {
    $import = TransactionImport::factory()->create();
    
    expect($import)->toBeInstanceOf(TransactionImport::class);
    expect($import->entity)->toBeInstanceOf(Entity::class);
    expect($import->import_type)->toBeInstanceOf(\App\Enums\ImportType::class);
    expect($import->status)->toBeInstanceOf(\App\Enums\ImportStatus::class);
});

test('import has helper methods', function () {
    $import = TransactionImport::factory()->create(['status' => 'Imported']);
    
    expect($import->getTypeLabel())->toBeString();
    expect($import->getStatusLabel())->toBeString();
    expect($import->isComplete())->toBeTrue();
    expect($import->isSuccess())->toBeTrue();
});

test('import can update status', function () {
    $import = TransactionImport::factory()->create(['status' => 'Processing']);
    
    $import->updateStatus(\App\Enums\ImportStatus::Imported, 100);
    
    expect($import->status)->toBe(\App\Enums\ImportStatus::Imported);
    expect($import->row_count)->toBe(100);
});