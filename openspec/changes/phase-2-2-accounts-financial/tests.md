# Testing Strategy: Accounts & Financial Structure (Phase 2.2)

## Testing Overview

Comprehensive testing strategy for the financial system core, covering unit tests, integration tests, and end-to-end scenarios with special attention to multi-currency behavior.

## Test Architecture

### Testing Layers

1. **Unit Tests** - Individual model/service behavior
2. **Integration Tests** - Cross-component interaction
3. **Feature Tests** - End-to-end workflows
4. **Performance Tests** - Large dataset handling

### Test Data Strategy

- **Factories** for all models with realistic data generation
- **Seeders** for consistent test category data  
- **Traits** for common test scenarios (multi-currency, imports)
- **Mocked Services** for external dependencies (FxRateService)

## Unit Test Specifications

### Account Model Tests

**File:** `tests/Unit/Models/AccountTest.php`

```php
class AccountTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_entity(): void
    {
        $account = Account::factory()->create();
        
        $this->assertInstanceOf(Entity::class, $account->entity);
        $this->assertEquals($account->entity_id, $account->entity->id);
    }

    /** @test */
    public function it_belongs_to_currency(): void
    {
        $account = Account::factory()->create();
        
        $this->assertInstanceOf(Currency::class, $account->currency);
        $this->assertEquals($account->currency_id, $account->currency->id);
    }

    /** @test */
    public function it_has_many_transactions(): void
    {
        $account = Account::factory()->create();
        $transactions = Transaction::factory(3)->create(['account_id' => $account->id]);
        
        $this->assertCount(3, $account->transactions);
        $this->assertInstanceOf(Transaction::class, $account->transactions->first());
    }

    /** @test */
    public function account_number_is_encrypted(): void
    {
        $account = Account::factory()->create(['account_number' => '1234567890']);
        
        // Raw database value should be encrypted
        $rawValue = DB::table('accounts')->where('id', $account->id)->value('account_number');
        $this->assertNotEquals('1234567890', $rawValue);
        
        // Model accessor should decrypt
        $this->assertEquals('1234567890', $account->account_number);
    }

    /** @test */
    public function account_type_casts_to_enum(): void
    {
        $account = Account::factory()->create(['account_type' => 'Checking']);
        
        $this->assertInstanceOf(AccountType::class, $account->account_type);
        $this->assertEquals(AccountType::Checking, $account->account_type);
    }

    /** @test */
    public function status_casts_to_enum(): void
    {
        $account = Account::factory()->create(['status' => 'Active']);
        
        $this->assertInstanceOf(AccountStatus::class, $account->status);
        $this->assertEquals(AccountStatus::Active, $account->status);
    }

    /** @test */
    public function opening_balance_has_correct_precision(): void
    {
        $account = Account::factory()->create(['balance_opening' => 1234.567]);
        
        // Should round to 2 decimal places
        $this->assertEquals('1234.57', (string) $account->balance_opening);
    }

    /** @test */
    public function it_morphs_to_addresses(): void
    {
        $account = Account::factory()->create();
        $address = Address::factory()->create([
            'addressable_type' => Account::class,
            'addressable_id' => $account->id,
        ]);
        
        $this->assertCount(1, $account->addresses);
        $this->assertEquals($address->id, $account->addresses->first()->id);
    }
}
```

### Transaction Model Tests

**File:** `tests/Unit/Models/TransactionTest.php`

```php
class TransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_casts_date_properly(): void
    {
        $transaction = Transaction::factory()->create(['date' => '2024-01-15']);
        
        $this->assertInstanceOf(Carbon::class, $transaction->date);
        $this->assertEquals('2024-01-15', $transaction->date->format('Y-m-d'));
    }

    /** @test */
    public function it_casts_currency_amounts_with_correct_precision(): void
    {
        $transaction = Transaction::factory()->create([
            'amount_usd' => 1234.567,
            'amount_eur' => 1234.567,
            'amount_cop' => 1234567.89,
        ]);
        
        // USD and EUR: 2 decimal places
        $this->assertEquals('1234.57', (string) $transaction->amount_usd);
        $this->assertEquals('1234.57', (string) $transaction->amount_eur);
        
        // COP: 0 decimal places (rounded)
        $this->assertEquals('1234568', (string) $transaction->amount_cop);
    }

    /** @test */
    public function original_currency_casts_to_enum(): void
    {
        $transaction = Transaction::factory()->create(['original_currency' => 'USD']);
        
        $this->assertInstanceOf(Currency::class, $transaction->original_currency);
        $this->assertEquals(Currency::USD, $transaction->original_currency);
    }

    /** @test */
    public function get_original_amount_returns_amount_in_original_currency(): void
    {
        // EUR transaction
        $transaction = Transaction::factory()->create([
            'original_currency' => 'EUR',
            'amount_eur' => 1500.00,
            'amount_usd' => 1631.25,
        ]);
        
        $this->assertEquals('1500.00', (string) $transaction->getOriginalAmount());
        
        // USD transaction
        $transaction = Transaction::factory()->create([
            'original_currency' => 'USD',
            'amount_usd' => 2500.00,
            'amount_eur' => null,
        ]);
        
        $this->assertEquals('2500.00', (string) $transaction->getOriginalAmount());
    }

    /** @test */
    public function get_amount_in_returns_amount_for_specified_currency(): void
    {
        $transaction = Transaction::factory()->create([
            'amount_usd' => 1631.25,
            'amount_eur' => 1500.00,
            'amount_cop' => null,
        ]);
        
        $this->assertEquals('1631.25', (string) $transaction->getAmountIn(Currency::USD));
        $this->assertEquals('1500.00', (string) $transaction->getAmountIn(Currency::EUR));
        $this->assertNull($transaction->getAmountIn(Currency::COP));
    }

    /** @test */
    public function it_belongs_to_account(): void
    {
        $transaction = Transaction::factory()->create();
        
        $this->assertInstanceOf(Account::class, $transaction->account);
    }

    /** @test */
    public function it_belongs_to_category(): void
    {
        $category = TransactionCategory::factory()->create();
        $transaction = Transaction::factory()->create(['category_id' => $category->id]);
        
        $this->assertInstanceOf(TransactionCategory::class, $transaction->category);
        $this->assertEquals($category->id, $transaction->category->id);
    }

    /** @test */
    public function category_can_be_null(): void
    {
        $transaction = Transaction::factory()->create(['category_id' => null]);
        
        $this->assertNull($transaction->category);
    }

    /** @test */
    public function it_belongs_to_import(): void
    {
        $import = TransactionImport::factory()->create();
        $transaction = Transaction::factory()->create(['import_id' => $import->id]);
        
        $this->assertInstanceOf(TransactionImport::class, $transaction->import);
        $this->assertEquals($import->id, $transaction->import->id);
    }
}
```

### TransactionCategory Model Tests

**File:** `tests/Unit/Models/TransactionCategoryTest.php`

```php
class TransactionCategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function category_type_casts_to_enum(): void
    {
        $category = TransactionCategory::factory()->create(['category_type' => 'Income']);
        
        $this->assertInstanceOf(TransactionCategoryType::class, $category->category_type);
        $this->assertEquals(TransactionCategoryType::Income, $category->category_type);
    }

    /** @test */
    public function is_system_casts_to_boolean(): void
    {
        $systemCategory = TransactionCategory::factory()->create(['is_system' => true]);
        $customCategory = TransactionCategory::factory()->create(['is_system' => false]);
        
        $this->assertTrue($systemCategory->is_system);
        $this->assertFalse($customCategory->is_system);
    }

    /** @test */
    public function it_has_many_transactions(): void
    {
        $category = TransactionCategory::factory()->create();
        $transactions = Transaction::factory(3)->create(['category_id' => $category->id]);
        
        $this->assertCount(3, $category->transactions);
        $this->assertInstanceOf(Transaction::class, $category->transactions->first());
    }

    /** @test */
    public function code_must_be_unique(): void
    {
        TransactionCategory::factory()->create(['code' => 'UNIQUE_CODE']);
        
        $this->expectException(QueryException::class);
        TransactionCategory::factory()->create(['code' => 'UNIQUE_CODE']);
    }
}
```

### TransactionImport Model Tests

**File:** `tests/Unit/Models/TransactionImportTest.php`

```php
class TransactionImportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function import_type_casts_to_enum(): void
    {
        $import = TransactionImport::factory()->create(['import_type' => 'CSV']);
        
        $this->assertInstanceOf(ImportType::class, $import->import_type);
        $this->assertEquals(ImportType::CSV, $import->import_type);
    }

    /** @test */
    public function status_casts_to_enum(): void
    {
        $import = TransactionImport::factory()->create(['status' => 'Imported']);
        
        $this->assertInstanceOf(ImportStatus::class, $import->status);
        $this->assertEquals(ImportStatus::Imported, $import->status);
    }

    /** @test */
    public function import_date_casts_to_timestamp(): void
    {
        $import = TransactionImport::factory()->create(['import_date' => '2024-01-15 10:30:00']);
        
        $this->assertInstanceOf(Carbon::class, $import->import_date);
    }

    /** @test */
    public function it_belongs_to_entity(): void
    {
        $import = TransactionImport::factory()->create();
        
        $this->assertInstanceOf(Entity::class, $import->entity);
    }

    /** @test */
    public function it_has_many_transactions(): void
    {
        $import = TransactionImport::factory()->create();
        $transactions = Transaction::factory(5)->create(['import_id' => $import->id]);
        
        $this->assertCount(5, $import->transactions);
        $this->assertInstanceOf(Transaction::class, $import->transactions->first());
    }
}
```

## Service Tests

### CurrencyConversionService Tests

**File:** `tests/Unit/Services/CurrencyConversionServiceTest.php`

```php
class CurrencyConversionServiceTest extends TestCase
{
    use RefreshDatabase;

    private CurrencyConversionService $service;
    private MockObject $fxRateService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->fxRateService = $this->createMock(FxRateService::class);
        $this->service = new CurrencyConversionService($this->fxRateService);
    }

    /** @test */
    public function it_converts_transaction_to_target_currency(): void
    {
        $transaction = Transaction::factory()->create([
            'original_currency' => 'EUR',
            'amount_eur' => 1500.00,
            'amount_usd' => null,
        ]);
        
        $this->fxRateService
            ->expects($this->once())
            ->method('findOrFetchRate')
            ->with(Currency::EUR, Currency::USD, $transaction->date)
            ->willReturn(1.0875);
        
        $result = $this->service->convert($transaction, Currency::USD);
        
        $this->assertEquals('1631.25', (string) $result);
        
        // Check that amount was cached
        $transaction->refresh();
        $this->assertEquals('1631.25', (string) $transaction->amount_usd);
    }

    /** @test */
    public function it_returns_cached_conversion_without_rate_lookup(): void
    {
        $transaction = Transaction::factory()->create([
            'original_currency' => 'EUR',
            'amount_eur' => 1500.00,
            'amount_usd' => 1631.25, // Already converted
        ]);
        
        $this->fxRateService
            ->expects($this->never())
            ->method('findOrFetchRate');
        
        $result = $this->service->convert($transaction, Currency::USD);
        
        $this->assertEquals('1631.25', (string) $result);
    }

    /** @test */
    public function it_returns_original_amount_for_original_currency(): void
    {
        $transaction = Transaction::factory()->create([
            'original_currency' => 'EUR',
            'amount_eur' => 1500.00,
            'amount_usd' => null,
        ]);
        
        $this->fxRateService
            ->expects($this->never())
            ->method('findOrFetchRate');
        
        $result = $this->service->convert($transaction, Currency::EUR);
        
        $this->assertEquals('1500.00', (string) $result);
    }

    /** @test */
    public function it_respects_manual_overrides(): void
    {
        $transaction = Transaction::factory()->create([
            'original_currency' => 'EUR',
            'amount_eur' => 1500.00,
            'amount_usd' => 1650.00, // Manual override
        ]);
        
        $this->fxRateService
            ->expects($this->never())
            ->method('findOrFetchRate');
        
        $result = $this->service->convert($transaction, Currency::USD);
        
        $this->assertEquals('1650.00', (string) $result);
    }

    /** @test */
    public function it_handles_cop_precision_correctly(): void
    {
        $transaction = Transaction::factory()->create([
            'original_currency' => 'USD',
            'amount_usd' => 1000.00,
            'amount_cop' => null,
        ]);
        
        $this->fxRateService
            ->expects($this->once())
            ->method('findOrFetchRate')
            ->with(Currency::USD, Currency::COP, $transaction->date)
            ->willReturn(4250.75);
        
        $result = $this->service->convert($transaction, Currency::COP);
        
        // Should be rounded to no decimals
        $this->assertEquals('4250750', (string) $result);
        
        $transaction->refresh();
        $this->assertEquals('4250750', (string) $transaction->amount_cop);
    }

    /** @test */
    public function it_converts_batch_of_transactions(): void
    {
        $transactions = collect([
            Transaction::factory()->create([
                'original_currency' => 'EUR',
                'amount_eur' => 1000.00,
                'amount_usd' => null,
            ]),
            Transaction::factory()->create([
                'original_currency' => 'EUR',
                'amount_eur' => 2000.00,
                'amount_usd' => null,
            ]),
        ]);
        
        $this->fxRateService
            ->expects($this->exactly(2))
            ->method('findOrFetchRate')
            ->willReturn(1.08);
        
        $results = $this->service->convertBatch($transactions, Currency::USD);
        
        $this->assertCount(2, $results);
        $this->assertEquals('1080.00', (string) $results[0]);
        $this->assertEquals('2160.00', (string) $results[1]);
    }

    /** @test */
    public function it_throws_exception_when_fx_rate_not_available(): void
    {
        $transaction = Transaction::factory()->create([
            'original_currency' => 'EUR',
            'amount_eur' => 1500.00,
            'amount_usd' => null,
        ]);
        
        $this->fxRateService
            ->expects($this->once())
            ->method('findOrFetchRate')
            ->willThrowException(new FxRateException('Rate not available'));
        
        $this->expectException(FxRateException::class);
        $this->service->convert($transaction, Currency::USD);
    }
}
```

## Integration Tests

### Multi-Currency Transaction Workflow

**File:** `tests/Integration/MultiCurrencyWorkflowTest.php`

```php
class MultiCurrencyWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function complete_multi_currency_transaction_workflow(): void
    {
        // Setup: Entity with EUR account
        $entity = Entity::factory()->create();
        $eurAccount = Account::factory()->create([
            'entity_id' => $entity->id,
            'currency_id' => Currency::where('code', 'EUR')->first()->id,
        ]);
        
        // Create EUR transaction
        $transaction = Transaction::factory()->create([
            'account_id' => $eurAccount->id,
            'original_currency' => 'EUR',
            'amount_eur' => 1500.00,
            'amount_usd' => null,
            'amount_cop' => null,
        ]);
        
        // Assign category
        $category = TransactionCategory::factory()->create(['category_type' => 'Income']);
        $transaction->update(['category_id' => $category->id]);
        
        // Convert to USD for US tax report
        $conversionService = app(CurrencyConversionService::class);
        $usdAmount = $conversionService->convert($transaction, Currency::USD);
        
        $this->assertGreaterThan(0, $usdAmount);
        $transaction->refresh();
        $this->assertNotNull($transaction->amount_usd);
        
        // Convert to COP for Colombian report
        $copAmount = $conversionService->convert($transaction, Currency::COP);
        
        $this->assertGreaterThan(0, $copAmount);
        $transaction->refresh();
        $this->assertNotNull($transaction->amount_cop);
        
        // Verify original amount unchanged
        $this->assertEquals('1500.00', (string) $transaction->amount_eur);
        $this->assertEquals(Currency::EUR, $transaction->original_currency);
    }
}
```

### Import Batch Processing

**File:** `tests/Integration/ImportBatchTest.php`

```php
class ImportBatchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function import_batch_processes_transactions_correctly(): void
    {
        $entity = Entity::factory()->create();
        $account = Account::factory()->create(['entity_id' => $entity->id]);
        
        // Create import batch
        $import = TransactionImport::factory()->create([
            'entity_id' => $entity->id,
            'import_type' => ImportType::CSV,
            'status' => ImportStatus::Processing,
        ]);
        
        // Create transactions associated with import
        $transactions = Transaction::factory(5)->create([
            'account_id' => $account->id,
            'import_id' => $import->id,
        ]);
        
        // Update import status
        $import->update([
            'status' => ImportStatus::Imported,
            'row_count' => $transactions->count(),
        ]);
        
        $this->assertEquals(5, $import->transactions->count());
        $this->assertEquals(ImportStatus::Imported, $import->status);
        $this->assertEquals(5, $import->row_count);
    }
}
```

## Feature Tests

### Transaction Management

**File:** `tests/Feature/TransactionManagementTest.php`

```php
class TransactionManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_create_transaction_with_category(): void
    {
        $user = User::factory()->create();
        $entity = Entity::factory()->create(['user_id' => $user->id]);
        $account = Account::factory()->create(['entity_id' => $entity->id]);
        $category = TransactionCategory::factory()->create();
        
        $response = $this->actingAs($user)->post('/transactions', [
            'account_id' => $account->id,
            'date' => '2024-01-15',
            'description' => 'Consulting payment',
            'amount' => 2500.00,
            'category_id' => $category->id,
        ]);
        
        $response->assertStatus(201);
        
        $transaction = Transaction::first();
        $this->assertEquals($account->id, $transaction->account_id);
        $this->assertEquals($category->id, $transaction->category_id);
        $this->assertEquals('2500.00', (string) $transaction->getOriginalAmount());
    }

    /** @test */
    public function user_cannot_create_transaction_for_other_users_account(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherAccount = Account::factory()->create([
            'entity_id' => Entity::factory()->create(['user_id' => $otherUser->id]),
        ]);
        
        $response = $this->actingAs($user)->post('/transactions', [
            'account_id' => $otherAccount->id,
            'date' => '2024-01-15',
            'description' => 'Unauthorized transaction',
            'amount' => 100.00,
        ]);
        
        $response->assertStatus(403);
    }
}
```

## Performance Tests

### Large Dataset Handling

**File:** `tests/Performance/LargeDatasetTest.php`

```php
class LargeDatasetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function batch_currency_conversion_performs_efficiently(): void
    {
        $account = Account::factory()->create();
        $transactions = Transaction::factory(1000)->create(['account_id' => $account->id]);
        
        $startTime = microtime(true);
        
        $conversionService = app(CurrencyConversionService::class);
        $conversionService->convertBatch($transactions, Currency::USD);
        
        $executionTime = microtime(true) - $startTime;
        
        // Should complete within 5 seconds
        $this->assertLessThan(5.0, $executionTime);
    }

    /** @test */
    public function querying_large_transaction_sets_is_optimized(): void
    {
        $account = Account::factory()->create();
        Transaction::factory(10000)->create(['account_id' => $account->id]);
        
        $startTime = microtime(true);
        
        // Query should use indexes
        $result = Transaction::where('account_id', $account->id)
            ->whereBetween('date', ['2024-01-01', '2024-12-31'])
            ->with('category')
            ->paginate(50);
        
        $executionTime = microtime(true) - $startTime;
        
        // Should complete within 1 second
        $this->assertLessThan(1.0, $executionTime);
        $this->assertCount(50, $result->items());
    }
}
```

## Error Handling Tests

### Currency Conversion Errors

**File:** `tests/Unit/Services/CurrencyConversionErrorTest.php`

```php
class CurrencyConversionErrorTest extends TestCase
{
    /** @test */
    public function handles_missing_fx_rate_gracefully(): void
    {
        $fxRateService = $this->createMock(FxRateService::class);
        $fxRateService->method('findOrFetchRate')
            ->willThrowException(new FxRateException('Rate not available for date'));
        
        $service = new CurrencyConversionService($fxRateService);
        $transaction = Transaction::factory()->create([
            'original_currency' => 'EUR',
            'amount_eur' => 1000.00,
            'amount_usd' => null,
        ]);
        
        $this->expectException(FxRateException::class);
        $service->convert($transaction, Currency::USD);
    }

    /** @test */
    public function handles_invalid_currency_gracefully(): void
    {
        $transaction = Transaction::factory()->create([
            'original_currency' => 'EUR',
            'amount_eur' => 1000.00,
        ]);
        
        $this->expectException(InvalidArgumentException::class);
        $transaction->getAmountIn('INVALID');
    }
}
```

## Test Data Management

### Database Traits

**File:** `tests/Concerns/CreatesFinancialData.php`

```php
trait CreatesFinancialData
{
    protected function createCompleteFinancialSetup(): array
    {
        $user = User::factory()->create();
        $entity = Entity::factory()->create(['user_id' => $user->id]);
        $account = Account::factory()->create(['entity_id' => $entity->id]);
        $categories = TransactionCategory::factory(5)->create();
        
        return compact('user', 'entity', 'account', 'categories');
    }

    protected function createMultiCurrencyTransaction(): Transaction
    {
        return Transaction::factory()->create([
            'original_currency' => 'EUR',
            'amount_eur' => 1500.00,
            'amount_usd' => 1631.25,
            'amount_cop' => null,
        ]);
    }

    protected function createImportBatch(int $transactionCount = 5): TransactionImport
    {
        $import = TransactionImport::factory()->create(['row_count' => $transactionCount]);
        
        Transaction::factory($transactionCount)->create(['import_id' => $import->id]);
        
        return $import;
    }
}
```

## Test Coverage Targets

### Coverage Requirements

- **Overall Coverage:** 100% (enforced by existing setup)
- **Line Coverage:** 100%
- **Branch Coverage:** 95%+
- **Method Coverage:** 100%

### Critical Path Coverage

1. **Multi-Currency Conversion** - All conversion scenarios tested
2. **Data Relationships** - All Eloquent relationships tested
3. **Business Logic** - All service methods tested
4. **Error Handling** - All exception scenarios tested
5. **Performance** - Large dataset scenarios tested

## Test Execution Strategy

### Local Development

```bash
# Run specific test suites
composer test:unit
composer test:feature
composer test:coverage

# Run specific test files
php artisan test tests/Unit/Models/TransactionTest.php
php artisan test tests/Feature/TransactionManagementTest.php

# Run with coverage
php artisan test --coverage
```

### CI/CD Pipeline

```yaml
# GitHub Actions example
- name: Run Test Suite
  run: |
    composer test:coverage
    composer test:types
    composer lint
```

## Mock and Stub Strategy

### External Service Mocking

- **FxRateService** - Mock for unit tests, real for integration
- **ECB API** - Always mocked (external dependency)
- **File System** - Mock for import tests

### Database Strategy

- **RefreshDatabase** for all feature/integration tests
- **DatabaseTransactions** for unit tests when possible
- **Factories** over fixtures for data generation

## Success Criteria

### Test Suite Completion

- [ ] All model tests passing with 100% coverage
- [ ] All service tests passing with comprehensive scenarios
- [ ] All integration tests covering end-to-end workflows
- [ ] All performance tests meeting benchmarks
- [ ] All error handling tests covering edge cases

### Quality Gates

- [ ] PHPStan level 9 with no errors
- [ ] 100% type coverage
- [ ] All tests running in under 60 seconds locally
- [ ] No flaky tests in CI/CD pipeline