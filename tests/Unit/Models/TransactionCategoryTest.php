<?php

declare(strict_types=1);

use App\Models\TransactionCategory;

test('transaction category can be created', function () {
    $category = TransactionCategory::factory()->create();
    
    expect($category)->toBeInstanceOf(TransactionCategory::class);
    expect($category->category_type)->toBeInstanceOf(\App\Enums\TransactionCategoryType::class);
});

test('system categories cannot be deleted', function () {
    $systemCategory = TransactionCategory::factory()->create(['is_system' => true]);
    $customCategory = TransactionCategory::factory()->create(['is_system' => false]);
    
    expect($systemCategory->canBeDeleted())->toBeFalse();
    expect($customCategory->canBeDeleted())->toBeTrue();
});

test('category has correct helper methods', function () {
    $category = TransactionCategory::factory()->create();
    
    expect($category->getTypeLabel())->toBeString();
    expect($category->getTypeColor())->toBeString();
    expect($category->getTypeIcon())->toBeString();
});