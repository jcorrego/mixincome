# Specification: Transaction Category Management

## Purpose

Transaction categories map financial transactions to tax-relevant classifications for reporting. Categories are either system-provided (pre-seeded) or user-created custom categories.

## Core Requirements

### TC-01: Category Structure

The system SHALL provide transaction categories with these properties:

- **Code** - Unique identifier (e.g., "BUS_INCOME", "RENTAL_EXPENSE")
- **Name** - Human-readable name (e.g., "Business Income", "Rental Expense")  
- **Category Type** - One of: Income, Expense, Transfer, Tax, Other
- **Description** - Optional detailed explanation
- **System Flag** - Indicates if category is pre-seeded vs user-created

#### Scenario: System Category Properties

```
GIVEN the system is initialized
WHEN I examine the "Business Income" category
THEN it has:
  - Code: "BUS_INCOME"
  - Name: "Business Income"
  - Category Type: Income
  - Description: "Income from business operations"
  - Is System: true
```

### TC-02: System Category Seeding

#### Scenario: Income Categories Available

```
GIVEN a fresh installation
THEN the following Income categories exist:
  - Business Income (BUS_INCOME)
  - Rental Income (RENTAL_INCOME)  
  - Interest Income (INTEREST_INCOME)
  - Dividend Income (DIVIDEND_INCOME)
  - Capital Gains (CAPITAL_GAINS)
  - Other Income (OTHER_INCOME)
```

#### Scenario: Expense Categories Available

```
GIVEN a fresh installation  
THEN the following Expense categories exist:
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
```

#### Scenario: Transfer Categories Available

```
GIVEN a fresh installation
THEN the following Transfer categories exist:
  - Account Transfer (ACCOUNT_TRANSFER)
  - Investment Transfer (INVESTMENT_TRANSFER)  
  - Loan Payment (LOAN_PAYMENT)
  - Credit Card Payment (CC_PAYMENT)
```

#### Scenario: Tax Categories Available

```
GIVEN a fresh installation
THEN the following Tax categories exist:
  - Estimated Tax Payment (EST_TAX_PAYMENT)
  - Withholding Tax (WITHHOLDING_TAX)
  - Tax Refund (TAX_REFUND)
```

### TC-03: Custom Category Creation

#### Scenario: Create Custom Income Category

```
GIVEN a user needs a specific income category
WHEN they create a custom category with:
  - Code: "CONSULTING_INCOME"
  - Name: "Consulting Income"
  - Category Type: Income
  - Description: "Income from consulting services"
THEN the category is created successfully
AND it has is_system = false
AND the code is unique across all categories
```

#### Scenario: Custom Category Code Uniqueness

```
GIVEN a category with code "CUSTOM_EXPENSE" exists
WHEN a user tries to create another category with code "CUSTOM_EXPENSE"
THEN the system prevents creation
AND shows error "Category code already exists"
```

### TC-04: Category Usage Tracking

#### Scenario: Categories with Transactions

```
GIVEN a category is used by one or more transactions
WHEN attempting to delete the category
THEN the system prevents deletion
AND shows error "Cannot delete category used by transactions"
AND suggests alternative actions (rename, deactivate)
```

#### Scenario: Unused Category Deletion

```
GIVEN a custom category with no associated transactions
WHEN the user deletes the category
THEN the category is removed successfully
AND no data integrity issues occur
```

### TC-05: Category Filtering and Search

#### Scenario: Filter by Category Type

```
GIVEN categories of multiple types exist
WHEN filtering by "Income" type
THEN only Income categories are returned
AND both system and custom Income categories are included
```

#### Scenario: Search Categories by Name

```
GIVEN categories exist with various names
WHEN searching for "rental"
THEN categories with "rental" in name or description are returned
AND search is case-insensitive
```

## Business Rules

### BR-01: System Category Protection

- System categories (is_system = true) CANNOT be deleted
- System categories CAN be renamed if needed
- System category codes CANNOT be changed
- System category types CANNOT be changed

### BR-02: Code Format Standards

- Category codes MUST be uppercase
- Category codes MUST use underscores (no spaces or special chars)
- Category codes MUST be 3-50 characters long
- Category codes MUST start with a letter

#### Scenario: Code Format Validation

```
GIVEN a user creates a category with code "rental income"
THEN the system transforms it to "RENTAL_INCOME" 
OR rejects it and suggests proper format

GIVEN a user creates a category with code "123INVALID"
THEN the system rejects it with error "Code must start with a letter"
```

### BR-03: Category Type Constraints

- Category type cannot be changed once transactions are assigned
- Transfer categories should be used for money movement between accounts
- Tax categories should be used for government-related payments/refunds

## Data Integrity

### DI-01: Unique Constraints

```sql
-- Category codes must be unique
code VARCHAR(50) NOT NULL UNIQUE

-- Category names should be unique (business rule, not DB constraint)
```

### DI-02: Required Fields

```sql
code VARCHAR(50) NOT NULL UNIQUE
name VARCHAR(255) NOT NULL
category_type ENUM('Income', 'Expense', 'Transfer', 'Tax', 'Other') NOT NULL
is_system BOOLEAN NOT NULL DEFAULT FALSE
```

### DI-03: Indexes for Performance

```sql
-- Query by category type
INDEX (category_type)

-- Query system vs custom categories  
INDEX (is_system)
```

## Transaction Relationships

### TR-01: Category Assignment

#### Scenario: Assign Category to Transaction

```
GIVEN a transaction exists without a category
WHEN assigning category "Business Income"  
THEN the transaction.category_id is updated
AND the category.transactions relationship includes this transaction
```

#### Scenario: Remove Category from Transaction

```
GIVEN a transaction with category "Business Expense"
WHEN removing the category assignment
THEN the transaction.category_id becomes NULL
AND the transaction appears as "Uncategorized"
```

### TR-02: Category Usage Statistics

#### Scenario: Most Used Categories

```
GIVEN multiple transactions with various categories
WHEN requesting category usage statistics
THEN categories are ordered by transaction count descending
AND usage counts are accurate
AND only categories with transactions > 0 are included
```

## Error Handling

### EH-01: Validation Errors

- **Invalid Code Format** - "Category code must be uppercase letters and underscores only"
- **Duplicate Code** - "Category code already exists"
- **Missing Required Fields** - "Category name and type are required"
- **Invalid Type** - "Category type must be one of: Income, Expense, Transfer, Tax, Other"

### EH-02: Business Logic Errors

- **Delete System Category** - "System categories cannot be deleted"
- **Delete Used Category** - "Cannot delete category used by N transactions"
- **Change Type with Transactions** - "Cannot change category type while transactions are assigned"