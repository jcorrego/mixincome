<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Entity;
use App\Models\Currency;

test('account can be created', function () {
    $account = Account::factory()->create();
    
    expect($account)->toBeInstanceOf(Account::class);
    expect($account->entity)->toBeInstanceOf(Entity::class);
    expect($account->currency)->toBeInstanceOf(Currency::class);
});

test('account has correct casts', function () {
    $account = Account::factory()->create();
    
    expect($account->account_type)->toBeInstanceOf(\App\Enums\AccountType::class);
    expect($account->status)->toBeInstanceOf(\App\Enums\AccountStatus::class);
});

test('account can check if it can create transactions', function () {
    $activeAccount = Account::factory()->create(['status' => 'Active']);
    $closedAccount = Account::factory()->create(['status' => 'Closed']);
    
    expect($activeAccount->canCreateTransactions())->toBeTrue();
    expect($closedAccount->canCreateTransactions())->toBeFalse();
});