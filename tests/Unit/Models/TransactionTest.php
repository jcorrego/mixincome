<?php

declare(strict_types=1);

use App\Enums\Currency;
use App\Models\Transaction;
use App\Models\Account;

test('transaction can be created', function () {
    $transaction = Transaction::factory()->create();
    
    expect($transaction)->toBeInstanceOf(Transaction::class);
    expect($transaction->account)->toBeInstanceOf(Account::class);
    expect($transaction->original_currency)->toBeInstanceOf(Currency::class);
});

test('transaction can get original amount', function () {
    $transaction = Transaction::factory()->create([
        'original_currency' => 'EUR',
        'amount_eur' => 1500.00,
    ]);
    
    expect($transaction->getOriginalAmount())->toBe('1500.00');
});

test('transaction can check currency amounts', function () {
    $transaction = Transaction::factory()->create([
        'original_currency' => 'USD',
        'amount_usd' => 1000.00,
        'amount_eur' => null,
    ]);
    
    expect($transaction->hasAmountIn(Currency::Usd))->toBeTrue();
    expect($transaction->hasAmountIn(Currency::Eur))->toBeFalse();
    expect($transaction->getAmountIn(Currency::Usd))->toBe('1000.00');
    expect($transaction->getAmountIn(Currency::Eur))->toBeNull();
});

test('transaction can detect income and expense', function () {
    $income = Transaction::factory()->create([
        'original_currency' => 'USD',
        'amount_usd' => 1000.00,
    ]);
    
    $expense = Transaction::factory()->create([
        'original_currency' => 'USD', 
        'amount_usd' => -500.00,
    ]);
    
    expect($income->isIncome())->toBeTrue();
    expect($income->isExpense())->toBeFalse();
    expect($expense->isIncome())->toBeFalse();
    expect($expense->isExpense())->toBeTrue();
});