# Tasks: Accounts & Financial Structure (Phase 2.2)

## Overview

Implementation tasks for creating the core financial tracking system with accounts, categories, transactions, and imports.

## Task Groups

### Group 1: Database Schema & Enums (Foundation)

#### Task 1.1: Create Account Enums
**Estimated Time:** 30 minutes

Create enum classes for account management:

```php
// app/Enums/AccountType.php
enum AccountType: string
{
    case Checking = 'Checking';
    case Savings = 'Savings';
    case CreditCard = 'CreditCard';
    case Investment = 'Investment';
    case Crypto = 'Crypto';
    case Cash = 'Cash';
    case Loan = 'Loan';
    case LineOfCredit = 'LineOfCredit';
}

// app/Enums/AccountStatus.php  
enum AccountStatus: string
{
    case Active = 'Active';
    case Inactive = 'Inactive';
    case Closed = 'Closed';
}
```

**Acceptance Criteria:**
- [ ] AccountType enum with 8 values
- [ ] AccountStatus enum with 3 values
- [ ] Enums follow project naming conventions
- [ ] Unit tests for enum values

---

#### Task 1.2: Create Transaction Enums
**Estimated Time:** 30 minutes

Create enum classes for transaction management:

```php
// app/Enums/TransactionCategoryType.php
enum TransactionCategoryType: string
{
    case Income = 'Income';
    case Expense = 'Expense';
    case Transfer = 'Transfer';
    case Tax = 'Tax';
    case Other = 'Other';
}

// app/Enums/ImportType.php
enum ImportType: string
{
    case CSV = 'CSV';
    case QIF = 'QIF';
    case PDF = 'PDF';
    case YNABSync = 'YNABSync';
    case MercuryAPI = 'MercuryAPI';
    case SantanderCSV = 'SantanderCSV';
    case BancolombiaSFTP = 'BancolombiaSFTP';
}

// app/Enums/ImportStatus.php
enum ImportStatus: string
{
    case Processing = 'Processing';
    case Imported = 'Imported';
    case Failed = 'Failed';
    case Duplicate = 'Duplicate';
    case Review = 'Review';
}
```

**Acceptance Criteria:**
- [ ] TransactionCategoryType enum with 5 values
- [ ] ImportType enum with 7 values  
- [ ] ImportStatus enum with 5 values
- [ ] Unit tests for all enums

---

#### Task 1.3: Create Accounts Migration
**Estimated Time:** 45 minutes

```sql
CREATE TABLE accounts (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  entity_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  account_type ENUM('Checking', 'Savings', 'CreditCard', 'Investment', 'Crypto', 'Cash', 'Loan', 'LineOfCredit') NOT NULL,
  currency_id BIGINT UNSIGNED NOT NULL,
  account_number TEXT NULL,
  balance_opening DECIMAL(15, 2) NULL,
  status ENUM('Active', 'Inactive', 'Closed') DEFAULT 'Active',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  
  FOREIGN KEY (entity_id) REFERENCES entities(id) ON DELETE CASCADE,
  FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE RESTRICT,
  INDEX (entity_id, status),
  INDEX (account_type)
);
```

**Acceptance Criteria:**
- [ ] Migration file created
- [ ] All columns with proper types
- [ ] Foreign key constraints
- [ ] Performance indexes
- [ ] Migration runs successfully
- [ ] Migration can be rolled back

---

#### Task 1.4: Create Transaction Categories Migration
**Estimated Time:** 30 minutes

```sql
CREATE TABLE transaction_categories (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  category_type ENUM('Income', 'Expense', 'Transfer', 'Tax', 'Other') NOT NULL,
  description TEXT NULL,
  is_system BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  
  INDEX (category_type),
  INDEX (is_system)
);
```

**Acceptance Criteria:**
- [ ] Migration file created
- [ ] Unique constraint on code
- [ ] Proper enum values
- [ ] Indexes for performance
- [ ] Migration runs successfully

---

#### Task 1.5: Create Transactions Migration  
**Estimated Time:** 45 minutes

```sql
CREATE TABLE transactions (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  account_id BIGINT UNSIGNED NOT NULL,
  category_id BIGINT UNSIGNED NULL,
  import_id BIGINT UNSIGNED NULL,
  date DATE NOT NULL,
  description TEXT NOT NULL,
  
  -- Multi-currency columns
  amount_usd DECIMAL(15, 2) NULL,
  amount_eur DECIMAL(15, 2) NULL,
  amount_cop DECIMAL(15, 0) NULL,
  
  original_currency VARCHAR(3) NOT NULL,
  
  notes TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  
  FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES transaction_categories(id) ON DELETE SET NULL,
  FOREIGN KEY (import_id) REFERENCES transaction_imports(id) ON DELETE SET NULL,
  INDEX (account_id, date),
  INDEX (date, category_id),
  INDEX (original_currency)
);
```

**Acceptance Criteria:**
- [ ] Migration file created
- [ ] Multi-currency columns with proper precision
- [ ] Foreign key constraints with proper cascading
- [ ] Performance indexes
- [ ] Migration runs successfully

---

#### Task 1.6: Create Transaction Imports Migration
**Estimated Time:** 30 minutes

```sql
CREATE TABLE transaction_imports (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  entity_id BIGINT UNSIGNED NOT NULL,
  import_type ENUM('CSV', 'QIF', 'PDF', 'YNABSync', 'MercuryAPI', 'SantanderCSV', 'BancolombiaSFTP') NOT NULL,
  file_name VARCHAR(255) NULL,
  import_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  row_count INT UNSIGNED DEFAULT 0,
  status ENUM('Processing', 'Imported', 'Failed', 'Duplicate', 'Review') DEFAULT 'Processing',
  error_message TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  
  FOREIGN KEY (entity_id) REFERENCES entities(id) ON DELETE CASCADE,
  INDEX (entity_id, status),
  INDEX (import_date)
);
```

**Acceptance Criteria:**
- [ ] Migration file created
- [ ] Proper enum values
- [ ] Default values set correctly
- [ ] Foreign key constraints
- [ ] Migration runs successfully

---

### Group 2: Models & Relationships

#### Task 2.1: Create Account Model
**Estimated Time:** 60 minutes

```php
class Account extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'entity_id',
        'name', 
        'account_type',
        'currency_id',
        'account_number',
        'balance_opening',
        'status'
    ];
    
    protected $casts = [
        'account_type' => AccountType::class,
        'status' => AccountStatus::class,
        'balance_opening' => 'decimal:2',
        'account_number' => 'encrypted'
    ];
    
    // Relationships
    public function entity(): BelongsTo;
    public function currency(): BelongsTo;
    public function transactions(): HasMany;
    public function addresses(): MorphMany;
}
```

**Acceptance Criteria:**
- [ ] Model created with proper fillable fields
- [ ] Enum casting for account_type and status
- [ ] Encrypted casting for account_number
- [ ] All relationships defined
- [ ] Factory created
- [ ] Unit tests for model behavior

---

#### Task 2.2: Create TransactionCategory Model
**Estimated Time:** 45 minutes

```php
class TransactionCategory extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'code',
        'name',
        'category_type', 
        'description',
        'is_system'
    ];
    
    protected $casts = [
        'category_type' => TransactionCategoryType::class,
        'is_system' => 'boolean'
    ];
    
    // Relationships
    public function transactions(): HasMany;
}
```

**Acceptance Criteria:**
- [ ] Model created with proper structure
- [ ] Enum casting for category_type
- [ ] Boolean casting for is_system
- [ ] Relationships defined
- [ ] Factory created
- [ ] Unit tests for model

---

#### Task 2.3: Create Transaction Model
**Estimated Time:** 90 minutes

Complex model with multi-currency support:

```php
class Transaction extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'account_id',
        'category_id',
        'import_id',
        'date',
        'description',
        'amount_usd',
        'amount_eur', 
        'amount_cop',
        'original_currency',
        'notes'
    ];
    
    protected $casts = [
        'date' => 'date',
        'amount_usd' => 'decimal:2',
        'amount_eur' => 'decimal:2',
        'amount_cop' => 'decimal:0',
        'original_currency' => Currency::class
    ];
    
    // Multi-currency methods
    public function getOriginalAmount(): Decimal;
    public function getAmountIn(Currency $currency): ?Decimal;
    
    // Relationships
    public function account(): BelongsTo;
    public function category(): BelongsTo;
    public function import(): BelongsTo;
}
```

**Acceptance Criteria:**
- [ ] Model created with multi-currency fields
- [ ] Proper decimal casting with currency-specific precision
- [ ] Multi-currency helper methods
- [ ] All relationships defined
- [ ] Factory created with multi-currency support
- [ ] Comprehensive unit tests

---

#### Task 2.4: Create TransactionImport Model
**Estimated Time:** 45 minutes

```php
class TransactionImport extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'entity_id',
        'import_type',
        'file_name',
        'import_date',
        'row_count',
        'status',
        'error_message'
    ];
    
    protected $casts = [
        'import_type' => ImportType::class,
        'import_date' => 'timestamp',
        'status' => ImportStatus::class,
        'row_count' => 'integer'
    ];
    
    // Relationships
    public function entity(): BelongsTo;
    public function transactions(): HasMany;
}
```

**Acceptance Criteria:**
- [ ] Model created with proper structure
- [ ] Enum casting for import_type and status
- [ ] Relationships defined
- [ ] Factory created
- [ ] Unit tests for model

---

### Group 3: Services & Business Logic

#### Task 3.1: Create CurrencyConversionService
**Estimated Time:** 120 minutes

Core service for multi-currency transaction support:

```php
class CurrencyConversionService
{
    public function __construct(private FxRateService $fxRateService) {}
    
    public function convert(Transaction $transaction, Currency $targetCurrency): Decimal;
    
    private function updateTransactionAmount(Transaction $transaction, Currency $currency, Decimal $amount): void;
    
    public function convertBatch(Collection $transactions, Currency $targetCurrency): Collection;
}
```

**Key Features:**
- Lazy conversion (check cache first)
- Integration with existing FxRateService
- Batch conversion support
- Manual override respect

**Acceptance Criteria:**
- [ ] Service created with dependency injection
- [ ] Converts individual transactions
- [ ] Supports batch conversion  
- [ ] Respects cached conversions
- [ ] Handles manual overrides correctly
- [ ] Comprehensive unit tests with mocked FxRateService
- [ ] Integration tests with real FxRateService

---

### Group 4: Data Seeding

#### Task 4.1: Create Transaction Category Seeder
**Estimated Time:** 60 minutes

Pre-populate system with standard tax categories:

```php
class TransactionCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Income Categories
        $this->createCategory('BUS_INCOME', 'Business Income', 'Income', 'Income from business operations');
        $this->createCategory('RENTAL_INCOME', 'Rental Income', 'Income', 'Income from rental properties');
        // ... ~40 total categories
    }
    
    private function createCategory(string $code, string $name, string $type, string $description): void;
}
```

**Categories to Create:**

**Income (6):**
- Business Income (BUS_INCOME)
- Rental Income (RENTAL_INCOME)
- Interest Income (INTEREST_INCOME)
- Dividend Income (DIVIDEND_INCOME)
- Capital Gains (CAPITAL_GAINS)
- Other Income (OTHER_INCOME)

**Expenses (15):**
- Business Expense (BUS_EXPENSE)
- Rental Expense (RENTAL_EXPENSE)
- Interest Expense (INTEREST_EXPENSE)
- Professional Fees (PROFESSIONAL_FEES)
- Travel & Meals (TRAVEL_MEALS)
- Home Office (HOME_OFFICE)
- Insurance (INSURANCE)
- Utilities (UTILITIES)
- Maintenance & Repairs (MAINTENANCE)
- Office Supplies (OFFICE_SUPPLIES)
- Software & Subscriptions (SOFTWARE_SUBS)
- Marketing & Advertising (MARKETING)
- Legal & Professional (LEGAL_PROF)
- Taxes & Licenses (TAXES_LICENSES)
- Other Expense (OTHER_EXPENSE)

**Transfers (4):**
- Account Transfer (ACCOUNT_TRANSFER)
- Investment Transfer (INVESTMENT_TRANSFER)
- Loan Payment (LOAN_PAYMENT)
- Credit Card Payment (CC_PAYMENT)

**Tax (3):**
- Estimated Tax Payment (EST_TAX_PAYMENT)
- Withholding Tax (WITHHOLDING_TAX)
- Tax Refund (TAX_REFUND)

**Acceptance Criteria:**
- [ ] Seeder creates all 28 system categories
- [ ] All categories marked as is_system = true
- [ ] Categories have proper codes, names, types
- [ ] Seeder is idempotent (can run multiple times)
- [ ] Seeder included in database seed chain

---

### Group 5: Testing Foundation

#### Task 5.1: Create Factories
**Estimated Time:** 90 minutes

**AccountFactory:**
```php
class AccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'entity_id' => Entity::factory(),
            'name' => $this->faker->company . ' ' . $this->faker->randomElement(['Checking', 'Savings']),
            'account_type' => $this->faker->randomElement(AccountType::cases()),
            'currency_id' => Currency::factory(), 
            'balance_opening' => $this->faker->randomFloat(2, 0, 50000),
            'status' => AccountStatus::Active,
        ];
    }
    
    public function checking(): static;
    public function creditCard(): static;
    public function closed(): static;
}
```

**TransactionFactory:**
```php
class TransactionFactory extends Factory  
{
    public function definition(): array
    {
        $currency = $this->faker->randomElement([Currency::USD, Currency::EUR, Currency::COP]);
        $amount = $this->faker->randomFloat(2, -5000, 5000);
        
        return [
            'account_id' => Account::factory(),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'description' => $this->faker->sentence,
            'original_currency' => $currency,
            $this->getCurrencyColumn($currency) => $amount,
        ];
    }
    
    public function income(): static;
    public function expense(): static;
    public function withCategory(): static;
    public function multiCurrency(): static;
}
```

**TransactionImportFactory:**
```php
class TransactionImportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'entity_id' => Entity::factory(),
            'import_type' => $this->faker->randomElement(ImportType::cases()),
            'file_name' => $this->faker->fileName,
            'row_count' => $this->faker->numberBetween(1, 500),
            'status' => ImportStatus::Imported,
        ];
    }
    
    public function processing(): static;
    public function failed(): static;
    public function csv(): static;
    public function api(): static;
}
```

**Acceptance Criteria:**
- [ ] All 4 factories created with realistic defaults
- [ ] Factories support state methods for testing scenarios
- [ ] Multi-currency factory logic for transactions
- [ ] Factories integrate with existing Entity/Currency factories
- [ ] Factory tests ensure data generation works

---

#### Task 5.2: Create Model Tests
**Estimated Time:** 120 minutes

**AccountTest:**
```php
class AccountTest extends TestCase
{
    public function test_belongs_to_entity(): void;
    public function test_belongs_to_currency(): void;
    public function test_has_many_transactions(): void;
    public function test_account_number_is_encrypted(): void;
    public function test_enum_casting_works(): void;
    public function test_opening_balance_precision(): void;
}
```

**TransactionTest:**
```php  
class TransactionTest extends TestCase
{
    public function test_multi_currency_amount_casting(): void;
    public function test_get_original_amount(): void;
    public function test_get_amount_in_currency(): void;
    public function test_cop_has_no_decimals(): void;
    public function test_belongs_to_account(): void;
    public function test_belongs_to_category(): void;
    public function test_belongs_to_import(): void;
}
```

**Acceptance Criteria:**
- [ ] Comprehensive model tests for all 4 models
- [ ] Relationship tests
- [ ] Enum casting tests
- [ ] Multi-currency logic tests
- [ ] Edge case tests (null values, precision, etc.)
- [ ] All tests pass with 100% coverage

---

### Group 6: Service Testing

#### Task 6.1: CurrencyConversionService Tests
**Estimated Time:** 90 minutes

```php
class CurrencyConversionServiceTest extends TestCase
{
    public function test_converts_transaction_to_target_currency(): void;
    public function test_returns_cached_conversion(): void;
    public function test_respects_manual_overrides(): void;
    public function test_batch_conversion(): void;
    public function test_original_currency_passthrough(): void;
    public function test_handles_missing_fx_rates(): void;
    public function test_cop_precision_handling(): void;
}
```

**Acceptance Criteria:**
- [ ] Service tests with mocked FxRateService
- [ ] Tests cover all conversion scenarios
- [ ] Tests verify caching behavior
- [ ] Tests check precision handling
- [ ] Integration tests with real FxRateService
- [ ] Performance tests for batch conversion

---

### Group 7: Integration & E2E Testing

#### Task 7.1: Full Transaction Workflow Tests
**Estimated Time:** 60 minutes

```php
class TransactionWorkflowTest extends TestCase
{
    public function test_create_account_and_transactions(): void;
    public function test_categorize_transactions(): void;
    public function test_convert_transactions_for_reporting(): void;
    public function test_import_batch_processing(): void;
}
```

**Acceptance Criteria:**
- [ ] End-to-end transaction workflows tested
- [ ] Multi-currency conversion in realistic scenarios
- [ ] Import batch processing integration
- [ ] Error handling in complex scenarios

---

### Group 8: Final Integration

#### Task 8.1: Update Existing Models
**Estimated Time:** 30 minutes

Add relationships to existing models:

```php
// Entity model
public function accounts(): HasMany 
{
    return $this->hasMany(Account::class);
}

public function transactionImports(): HasMany
{
    return $this->hasMany(TransactionImport::class);
}
```

**Acceptance Criteria:**
- [ ] Entity model updated with account relationships
- [ ] Address model supports morphing to Account
- [ ] Existing tests still pass
- [ ] New relationships tested

---

#### Task 8.2: Run Full Test Suite
**Estimated Time:** 15 minutes

```bash
composer test
```

**Acceptance Criteria:**
- [ ] All existing tests pass
- [ ] All new tests pass
- [ ] 100% type coverage maintained
- [ ] No PHPStan errors
- [ ] No lint errors

---

## Summary

**Total Estimated Time:** ~15 hours
**Total Tasks:** 18 tasks across 8 groups
**Key Deliverables:**
- 4 new models (Account, TransactionCategory, Transaction, TransactionImport)
- 4 database migrations
- 5 enum classes
- 1 service (CurrencyConversionService)
- 1 seeder (28 system categories)
- Comprehensive test suite
- Full integration with existing codebase

**Dependencies:**
- Existing Currency/FxRate system (Phase 2.1) ✅
- Existing Entity system (Phase 1.1-1.2) ✅

**Risk Mitigation:**
- Start with migrations and models (foundation)
- Test each component thoroughly before moving to next
- CurrencyConversionService is the most complex - allocate extra time if needed
- Run full test suite frequently to catch integration issues early