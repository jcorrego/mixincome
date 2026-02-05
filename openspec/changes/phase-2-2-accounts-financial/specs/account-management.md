# Specification: Account Management

## Purpose

Financial accounts represent bank accounts, credit cards, investment accounts, crypto wallets, and other financial instruments that hold money or assets. Each account belongs to an entity and tracks transactions in a specific currency.

## Core Requirements

### AC-01: Account Creation

The system SHALL allow users to create accounts with the following properties:

- **Entity Association** - Each account MUST belong to exactly one entity
- **Account Type** - Must be one of: Checking, Savings, CreditCard, Investment, Crypto, Cash, Loan, LineOfCredit  
- **Currency** - Each account MUST have a primary currency (USD, EUR, or COP)
- **Optional Details** - Account number (encrypted), opening balance, custom name

#### Scenario: Create Basic Bank Account

```
GIVEN a user with an entity "Juan Personal (Spain)"
WHEN they create a new account with:
  - Name: "Santander Checking"
  - Type: Checking  
  - Currency: EUR
  - Opening Balance: 5000.00
THEN the account is created successfully
AND the account belongs to the specified entity
AND the account has Active status by default
```

#### Scenario: Create Credit Card Account

```
GIVEN a user with an entity "JCO Services LLC"
WHEN they create a new account with:
  - Name: "Chase Sapphire Preferred"
  - Type: CreditCard
  - Currency: USD
  - Account Number: "****-****-****-1234"
THEN the account is created successfully
AND the account number is encrypted in storage
AND the opening balance defaults to null (credit cards track current balance elsewhere)
```

### AC-02: Account Relationships

#### Scenario: Account-Entity Association

```
GIVEN multiple entities exist
WHEN creating an account
THEN the account MUST be associated with exactly one entity
AND users can only create accounts for entities they own
AND deleting an entity cascades to delete its accounts
```

#### Scenario: Account-Currency Association

```
GIVEN the currencies USD, EUR, COP exist
WHEN creating an account
THEN the account MUST reference one of these currencies
AND the currency cannot be deleted while accounts reference it
```

### AC-03: Account Status Management

#### Scenario: Account Status Transitions

```
GIVEN an Active account
WHEN the user changes status to Inactive
THEN the account status updates to Inactive
AND the account still appears in lists but marked as inactive

GIVEN an Inactive account  
WHEN the user changes status to Closed
THEN the account status updates to Closed
AND the account appears in historical reports only

GIVEN a Closed account
WHEN the user tries to create new transactions
THEN the system prevents transaction creation
AND shows an appropriate error message
```

### AC-04: Account Security

#### Scenario: Account Number Encryption

```
GIVEN a user enters account number "1234567890123456"
WHEN the account is saved
THEN the account number is encrypted in the database
AND only authorized users can decrypt and view it
AND the account number is never logged in plain text
```

### AC-05: Account Queries

#### Scenario: List Accounts by Entity

```
GIVEN a user with multiple entities
AND each entity has multiple accounts
WHEN they request accounts for a specific entity
THEN only accounts belonging to that entity are returned
AND accounts are ordered by name ascending
```

#### Scenario: Filter Accounts by Type

```
GIVEN accounts of different types exist
WHEN filtering by account type "CreditCard"
THEN only credit card accounts are returned
AND the filter works across all user entities
```

## Business Rules

### BR-01: Account Ownership

- Users can only view/modify accounts for entities they own
- Accounts cannot be transferred between entities
- Deleting an entity cascades to delete its accounts

### BR-02: Currency Consistency

- An account's primary currency cannot be changed after creation
- All transactions in the account use the account's primary currency as base
- Currency conversion happens at the transaction level, not account level

### BR-03: Account Numbers

- Account numbers are optional (some account types like Cash don't have them)
- Account numbers are encrypted at rest
- Account numbers are masked in UI (show only last 4 digits)

## Data Integrity

### DI-01: Required Fields

```sql
-- These fields are required (NOT NULL)
entity_id BIGINT UNSIGNED NOT NULL
name VARCHAR(255) NOT NULL  
account_type ENUM(...) NOT NULL
currency_id BIGINT UNSIGNED NOT NULL
status ENUM(...) NOT NULL DEFAULT 'Active'
```

### DI-02: Foreign Key Constraints

```sql
-- Entity association (cascade delete)
FOREIGN KEY (entity_id) REFERENCES entities(id) ON DELETE CASCADE

-- Currency association (restrict delete)  
FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE RESTRICT
```

### DI-03: Indexes for Performance

```sql
-- Query by entity and status
INDEX (entity_id, status)

-- Query by account type
INDEX (account_type)
```

## Error Handling

### EH-01: Validation Errors

- **Invalid Entity** - "Entity not found or access denied"
- **Invalid Currency** - "Currency not supported"  
- **Invalid Account Type** - "Account type not recognized"
- **Missing Name** - "Account name is required"

### EH-02: Business Logic Errors

- **Duplicate Name** - "Account name already exists for this entity"
- **Closed Account Modification** - "Cannot modify closed accounts"
- **Invalid Status Transition** - "Invalid status change from X to Y"