# Design: Accounts & Financial Structure (Phase 2.2)

## Overview

This phase implements the core financial tracking system with 4 models: Account, TransactionCategory, Transaction, and TransactionImport. The system supports multi-currency transactions with lazy conversion using the existing FxRateService.

## Database Schema

### Accounts Table

```sql
CREATE TABLE accounts (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  entity_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  account_type ENUM('Checking', 'Savings', 'CreditCard', 'Investment', 'Crypto', 'Cash', 'Loan', 'LineOfCredit') NOT NULL,
  currency_id BIGINT UNSIGNED NOT NULL,
  account_number TEXT NULL, -- encrypted
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

### Transaction Categories Table

```sql
CREATE TABLE transaction_categories (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  code VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  category_type ENUM('Income', 'Expense', 'Transfer', 'Tax', 'Other') NOT NULL,
  description TEXT NULL,
  is_system BOOLEAN DEFAULT FALSE, -- pre-seeded vs user-created
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  
  INDEX (category_type),
  INDEX (is_system)
);
```

### Transactions Table (Multi-Currency)

```sql
CREATE TABLE transactions (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  account_id BIGINT UNSIGNED NOT NULL,
  category_id BIGINT UNSIGNED NULL, -- nullable for uncategorized
  import_id BIGINT UNSIGNED NULL, -- nullable for manual entries
  date DATE NOT NULL,
  description TEXT NOT NULL,
  
  -- Multi-currency columns (lazy-filled, nullable)
  amount_usd DECIMAL(15, 2) NULL,
  amount_eur DECIMAL(15, 2) NULL,
  amount_cop DECIMAL(15, 0) NULL,  -- COP without decimals
  
  original_currency VARCHAR(3) NOT NULL, -- 'USD', 'EUR', 'COP'
  
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

### Transaction Imports Table

```sql
CREATE TABLE transaction_imports (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  entity_id BIGINT UNSIGNED NOT NULL,
  import_type ENUM('CSV', 'QIF', 'PDF', 'YNABSync', 'MercuryAPI', 'SantanderCSV', 'BancolombiaSFTP') NOT NULL,
  file_name VARCHAR(255) NULL, -- nullable for API imports
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

## Models & Relationships

### Account Model

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
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }
    
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
    
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
    
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }
}
```

### TransactionCategory Model

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
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'category_id');
    }
}
```

### Transaction Model (Multi-Currency)

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
        'amount_cop' => 'decimal:0', // COP has no decimals
        'original_currency' => Currency::class
    ];
    
    // Relationships
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
    
    public function category(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class, 'category_id');
    }
    
    public function import(): BelongsTo
    {
        return $this->belongsTo(TransactionImport::class, 'import_id');
    }
    
    // Multi-currency helpers
    public function getOriginalAmount(): Decimal
    {
        return match ($this->original_currency) {
            Currency::USD => $this->amount_usd,
            Currency::EUR => $this->amount_eur,
            Currency::COP => $this->amount_cop,
        };
    }
    
    public function getAmountIn(Currency $currency): ?Decimal
    {
        return match ($currency) {
            Currency::USD => $this->amount_usd,
            Currency::EUR => $this->amount_eur,
            Currency::COP => $this->amount_cop,
        };
    }
}
```

### TransactionImport Model

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
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }
    
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'import_id');
    }
}
```

## Enums

```php
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

enum AccountStatus: string
{
    case Active = 'Active';
    case Inactive = 'Inactive';
    case Closed = 'Closed';
}

enum TransactionCategoryType: string
{
    case Income = 'Income';
    case Expense = 'Expense';
    case Transfer = 'Transfer';
    case Tax = 'Tax';
    case Other = 'Other';
}

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

enum ImportStatus: string
{
    case Processing = 'Processing';
    case Imported = 'Imported';
    case Failed = 'Failed';
    case Duplicate = 'Duplicate';
    case Review = 'Review';
}
```

## Services

### CurrencyConversionService

```php
class CurrencyConversionService
{
    public function __construct(
        private FxRateService $fxRateService
    ) {}
    
    public function convert(Transaction $transaction, Currency $targetCurrency): Decimal
    {
        // If already converted, return cached value
        $existingAmount = $transaction->getAmountIn($targetCurrency);
        if ($existingAmount !== null) {
            return $existingAmount;
        }
        
        // If target currency is the original, return original amount
        if ($transaction->original_currency === $targetCurrency) {
            $originalAmount = $transaction->getOriginalAmount();
            $this->updateTransactionAmount($transaction, $targetCurrency, $originalAmount);
            return $originalAmount;
        }
        
        // Convert via FxRateService
        $rate = $this->fxRateService->findOrFetchRate(
            from: $transaction->original_currency,
            to: $targetCurrency,
            date: $transaction->date
        );
        
        $convertedAmount = $transaction->getOriginalAmount() * $rate;
        
        // Cache the converted amount
        $this->updateTransactionAmount($transaction, $targetCurrency, $convertedAmount);
        
        return $convertedAmount;
    }
    
    private function updateTransactionAmount(Transaction $transaction, Currency $currency, Decimal $amount): void
    {
        $column = match ($currency) {
            Currency::USD => 'amount_usd',
            Currency::EUR => 'amount_eur',
            Currency::COP => 'amount_cop',
        };
        
        $transaction->update([$column => $amount]);
    }
}
```

## Data Seeding

### Transaction Categories (System)

Pre-seed with ~40 standard categories:

**Income:**
- Business Income (BUS_INCOME)
- Rental Income (RENTAL_INCOME)
- Interest Income (INTEREST_INCOME)
- Dividend Income (DIVIDEND_INCOME)
- Capital Gains (CAPITAL_GAINS)
- Other Income (OTHER_INCOME)

**Expenses:**
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

**Transfers:**
- Account Transfer (ACCOUNT_TRANSFER)
- Investment Transfer (INVESTMENT_TRANSFER)
- Loan Payment (LOAN_PAYMENT)
- Credit Card Payment (CC_PAYMENT)

**Tax:**
- Estimated Tax Payment (EST_TAX_PAYMENT)
- Withholding Tax (WITHHOLDING_TAX)
- Tax Refund (TAX_REFUND)

## Testing Strategy

### Model Tests
- Factory definitions for all 4 models
- Relationship tests
- Multi-currency amount calculations
- Enum casting

### Feature Tests  
- Account CRUD operations
- Transaction creation with currency conversion
- Import batch processing
- Category assignment

### Service Tests
- CurrencyConversionService with mocked FxRateService
- Lazy conversion scenarios
- Manual override scenarios

### Integration Tests
- Full transaction flow: create → categorize → convert → report
- Error handling for missing FX rates
- Duplicate transaction detection