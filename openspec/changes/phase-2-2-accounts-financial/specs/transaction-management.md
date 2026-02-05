# Specification: Transaction Management

## Purpose

Transactions represent individual financial movements (income, expenses, transfers) with multi-currency support. Each transaction belongs to an account and can be categorized for tax reporting.

## Core Requirements

### TM-01: Transaction Structure

The system SHALL support transactions with these properties:

- **Account Association** - Each transaction MUST belong to exactly one account
- **Date** - Transaction date (not necessarily creation date)
- **Description** - Descriptive text for the transaction
- **Multi-Currency Amounts** - Up to 3 currency amounts (USD, EUR, COP) with lazy conversion
- **Original Currency** - The currency in which the transaction was originally recorded
- **Category** - Optional tax-relevant category assignment
- **Import Batch** - Optional association with import batch
- **Notes** - Optional user notes

#### Scenario: Create Basic Transaction

```
GIVEN an account "Santander Checking" with currency EUR
WHEN creating a transaction with:
  - Date: 2024-01-15
  - Description: "Freelance payment from Client A"
  - Amount: 1500.00 EUR
  - Original Currency: EUR
THEN the transaction is created successfully
AND amount_eur = 1500.00
AND amount_usd = NULL (lazy conversion)
AND amount_cop = NULL (lazy conversion)
AND original_currency = "EUR"
```

### TM-02: Multi-Currency Lazy Conversion

#### Scenario: Convert Transaction to USD

```
GIVEN a transaction with:
  - Original: 1500.00 EUR
  - Date: 2024-01-15
  - amount_usd = NULL
WHEN requesting the USD amount
THEN the system finds EUR→USD rate for 2024-01-15
AND calculates: 1500.00 * 1.0875 = 1631.25
AND stores amount_usd = 1631.25
AND returns 1631.25 USD
```

#### Scenario: Convert Transaction to COP

```
GIVEN a transaction with:
  - Original: 1500.00 EUR
  - Date: 2024-01-15
  - amount_cop = NULL
WHEN requesting the COP amount
THEN the system finds EUR→COP rate for 2024-01-15
AND calculates: 1500.00 * 4750 = 7,125,000
AND stores amount_cop = 7125000 (no decimals for COP)
AND returns 7,125,000 COP
```

#### Scenario: Return Cached Conversion

```
GIVEN a transaction with amount_usd = 1631.25 already stored
WHEN requesting the USD amount again
THEN the cached value 1631.25 is returned immediately
AND no additional FX rate lookup occurs
```

### TM-03: Original Currency Handling

#### Scenario: USD Original Transaction

```
GIVEN a USD account "Chase Checking"
WHEN creating a transaction with 2500.00 USD
THEN amount_usd = 2500.00
AND original_currency = "USD"
AND this is both the original AND converted amount
```

#### Scenario: Return Original Amount

```
GIVEN a transaction with original_currency = "EUR" and amount_eur = 1500.00
WHEN requesting the original amount
THEN 1500.00 is returned
AND the original currency is clearly identified
```

### TM-04: Manual Currency Override

#### Scenario: User Overrides Converted Amount

```
GIVEN a transaction with calculated amount_usd = 1631.25
WHEN the user manually changes amount_usd to 1650.00
THEN the manual override is stored
AND future requests return 1650.00 (not re-calculated)
AND the original amount_eur remains unchanged
```

#### Scenario: Override Affects Only Target Currency

```
GIVEN a transaction with amount_usd manually set to 1650.00
WHEN requesting amount_cop for the first time
THEN COP is calculated from the original EUR amount
AND NOT from the overridden USD amount
```

### TM-05: Transaction Categorization

#### Scenario: Assign Category to Transaction

```
GIVEN an uncategorized transaction
AND a category "Business Income" exists
WHEN assigning the category to the transaction
THEN transaction.category_id = category.id
AND the transaction appears in category reports
```

#### Scenario: Change Transaction Category

```
GIVEN a transaction categorized as "Business Income"
WHEN changing the category to "Consulting Income"
THEN the category assignment is updated
AND historical category assignments are not tracked
```

#### Scenario: Remove Transaction Category

```
GIVEN a categorized transaction
WHEN removing the category assignment
THEN transaction.category_id = NULL
AND the transaction appears as "Uncategorized"
```

### TM-06: Transaction Import Association

#### Scenario: Imported Transaction

```
GIVEN a transaction import batch exists
WHEN creating transactions during import
THEN each transaction references the import batch
AND import statistics are updated (row_count, etc.)
```

#### Scenario: Manual Transaction

```
GIVEN a user manually creates a transaction
THEN transaction.import_id = NULL
AND the transaction is clearly marked as manual entry
```

## Multi-Currency Business Rules

### BR-01: Currency Conversion Logic

- Conversions always happen FROM original currency TO target currency
- Conversions use FxRateService with ECB rates for the transaction date
- If no rate exists for the exact date, use rate replication (up to 7 days prior)
- Manual overrides take precedence over calculated conversions
- Re-conversion only happens if cached amount is NULL

### BR-02: Currency Precision

- USD amounts: 2 decimal places (DECIMAL 15,2)
- EUR amounts: 2 decimal places (DECIMAL 15,2)  
- COP amounts: 0 decimal places (DECIMAL 15,0) - peso colombiano has no cents

#### Scenario: COP Precision Handling

```
GIVEN a transaction converts to 7,125,000.75 COP
WHEN storing the COP amount
THEN it is rounded to 7,125,001 COP (standard rounding)
AND no decimal places are stored
```

### BR-03: Original Currency Immutability

- Once a transaction is created, its original_currency cannot be changed
- The original amount (in original currency) cannot be automatically changed
- Users can manually edit the original amount if needed (data correction)

## Data Integrity

### DI-01: Required Fields

```sql
account_id BIGINT UNSIGNED NOT NULL
date DATE NOT NULL
description TEXT NOT NULL
original_currency VARCHAR(3) NOT NULL -- 'USD', 'EUR', 'COP'
```

### DI-02: Foreign Key Constraints

```sql
-- Account association (cascade delete)
FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE

-- Category association (set null when category deleted)
FOREIGN KEY (category_id) REFERENCES transaction_categories(id) ON DELETE SET NULL

-- Import association (set null when import deleted)
FOREIGN KEY (import_id) REFERENCES transaction_imports(id) ON DELETE SET NULL
```

### DI-03: Performance Indexes

```sql
-- Query transactions by account and date (most common)
INDEX (account_id, date)

-- Query transactions by date and category (reporting)
INDEX (date, category_id)

-- Query transactions by original currency
INDEX (original_currency)
```

### DI-04: Currency Amount Constraints

- At least ONE of amount_usd, amount_eur, amount_cop MUST be non-NULL
- The amount for original_currency MUST NOT be NULL
- All amount fields MUST be positive for expenses, negative for refunds/returns

#### Scenario: Amount Consistency Check

```
GIVEN a transaction with original_currency = "EUR"
THEN amount_eur MUST NOT be NULL
AND amount_usd MAY be NULL (lazy conversion)
AND amount_cop MAY be NULL (lazy conversion)
```

## Transaction Queries

### TQ-01: Account Transaction History

#### Scenario: Get Account Transactions

```
GIVEN an account with multiple transactions
WHEN querying transactions for the account
THEN transactions are returned in date descending order
AND each transaction includes account, category, and import information
AND pagination is supported for large transaction sets
```

### TQ-02: Multi-Currency Reporting

#### Scenario: Get Transactions in Specific Currency

```
GIVEN transactions in multiple original currencies
WHEN requesting all transactions in USD for date range
THEN each transaction shows its USD amount
AND lazy conversion happens for transactions without USD cached
AND manual overrides are respected
```

### TQ-03: Category-Based Queries

#### Scenario: Get Transactions by Category

```
GIVEN transactions across multiple categories
WHEN querying "Business Income" transactions for 2024
THEN only transactions with that category are returned
AND transactions include their original and converted amounts
```

## Error Handling

### EH-01: Validation Errors

- **Missing Account** - "Account is required and must exist"
- **Invalid Date** - "Transaction date must be a valid date"
- **Missing Description** - "Transaction description is required"
- **Invalid Currency** - "Original currency must be USD, EUR, or COP"
- **Missing Amount** - "Amount in original currency is required"

### EH-02: Conversion Errors

- **Missing FX Rate** - "Exchange rate not available for [date] [from]→[to]"
- **API Failure** - "Unable to fetch exchange rate, try again later"
- **Invalid Amount** - "Transaction amount must be a valid number"

### EH-03: Business Logic Errors

- **Closed Account** - "Cannot create transactions for closed accounts"
- **Future Date** - "Transaction date cannot be in the future"
- **Negative Amount** - "Amount must be positive (use categories to indicate direction)"