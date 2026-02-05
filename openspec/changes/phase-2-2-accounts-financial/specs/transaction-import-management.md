# Specification: Transaction Import Management

## Purpose

Transaction imports manage batches of transactions from external sources (CSV files, bank APIs, financial software exports). Each import tracks metadata, status, and associated transactions for better data management and error handling.

## Core Requirements

### TI-01: Import Batch Structure

The system SHALL support import batches with these properties:

- **Entity Association** - Each import MUST belong to exactly one entity
- **Import Type** - Source type (CSV, QIF, PDF, YNABSync, MercuryAPI, etc.)
- **File Information** - Original filename (for file-based imports)
- **Status Tracking** - Processing, Imported, Failed, Duplicate, Review
- **Row Counting** - Number of transactions processed
- **Error Handling** - Error messages for failed imports

#### Scenario: Create CSV Import Batch

```
GIVEN an entity "JCO Services LLC"
WHEN creating a new import batch with:
  - Import Type: CSV
  - File Name: "chase_transactions_2024_q1.csv"
  - Entity: JCO Services LLC
THEN the import batch is created
AND status = "Processing"
AND import_date = current timestamp
AND row_count = 0 (will be updated during processing)
```

### TI-02: Import Type Support

#### Scenario: File-Based Import Types

```
GIVEN the system supports file-based imports
THEN the following import types are available:
  - CSV (generic comma-separated values)
  - QIF (Quicken Interchange Format)
  - PDF (bank statement PDFs with OCR)
AND each type requires a file_name to be specified
```

#### Scenario: API-Based Import Types

```
GIVEN the system supports API-based imports
THEN the following import types are available:
  - YNABSync (YNAB API integration)
  - MercuryAPI (Mercury Bank API)
  - SantanderCSV (Santander bank CSV format)
  - BancolombiaSFTP (Bancolombia SFTP feed)
AND file_name may be NULL for API imports
```

### TI-03: Import Status Lifecycle

#### Scenario: Import Status Progression

```
GIVEN a new import batch
THEN initial status = "Processing"

WHEN processing completes successfully
THEN status = "Imported"
AND row_count reflects actual transactions created

WHEN processing encounters errors
THEN status = "Failed"  
AND error_message contains specific failure details

WHEN duplicate transactions are detected
THEN status = "Duplicate"
AND error_message explains which transactions are duplicates

WHEN manual review is needed
THEN status = "Review"
AND user must manually approve/reject transactions
```

#### Scenario: Status Cannot Regress

```
GIVEN an import with status "Imported"
WHEN attempting to change status to "Processing" 
THEN the change is rejected
AND error "Cannot change status from Imported to Processing"
```

### TI-04: Transaction Association

#### Scenario: Link Transactions to Import

```
GIVEN an import batch processes 25 transactions
WHEN the import completes successfully
THEN all 25 transactions have import_id = batch.id
AND the batch has row_count = 25
AND the batch has status = "Imported"
```

#### Scenario: Import Deletion Cascade

```
GIVEN an import batch with associated transactions
WHEN the import batch is deleted
THEN all associated transactions have import_id = NULL
AND transactions are NOT deleted (preserve data)
AND transactions can be identified as formerly imported
```

### TI-05: Duplicate Detection

#### Scenario: Detect Duplicate Transactions

```
GIVEN existing transactions:
  - Account: Chase Checking
  - Date: 2024-01-15
  - Description: "Amazon Purchase" 
  - Amount: -89.95 USD
WHEN importing a file containing the same transaction
THEN the import is marked as "Duplicate"
AND error_message = "Found 1 duplicate transaction"
AND no new transactions are created
```

#### Scenario: Partial Duplicates

```
GIVEN an import file with 10 transactions
AND 3 of them are duplicates of existing transactions
WHEN processing the import
THEN 7 new transactions are created
AND status = "Review" (partial success)
AND error_message = "3 duplicate transactions require review"
```

## Import Processing Rules

### BR-01: Entity Boundary Enforcement

- Imports can only create transactions for accounts within the same entity
- Cross-entity imports are not allowed
- Users must have access to the target entity

#### Scenario: Entity Boundary Check

```
GIVEN an import for entity "Personal Spain"
AND the import contains transactions for accounts not in that entity
WHEN processing the import
THEN those transactions are rejected
AND status = "Failed" 
AND error_message identifies invalid account references
```

### BR-02: Data Validation During Import

- All imported transactions must pass standard validation
- Account references must be valid and accessible
- Dates must be valid and not in future
- Amounts must be valid numbers

#### Scenario: Invalid Data Handling

```
GIVEN an import file with invalid transactions:
  - Row 5: Invalid date "2024-02-30"
  - Row 12: Invalid account "NonExistentAccount"
  - Row 18: Invalid amount "not-a-number"
WHEN processing the import
THEN status = "Failed"
AND error_message lists specific validation errors per row
AND no transactions are created
```

### BR-03: Currency Handling

- Imported amounts are assumed to be in the account's currency
- Original currency is set to account's currency
- Multi-currency conversions happen lazily (post-import)

#### Scenario: Currency Assignment

```
GIVEN a EUR account "Santander Checking"
AND an import with amount "1500.00"
WHEN creating the transaction
THEN amount_eur = 1500.00
AND original_currency = "EUR"
AND amount_usd = NULL (lazy conversion)
```

## Data Integrity

### DI-01: Required Fields

```sql
entity_id BIGINT UNSIGNED NOT NULL
import_type ENUM(...) NOT NULL
import_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
row_count INT UNSIGNED DEFAULT 0
status ENUM(...) DEFAULT 'Processing'
```

### DI-02: Optional Fields

```sql
file_name VARCHAR(255) NULL -- NULL for API imports
error_message TEXT NULL -- NULL for successful imports
```

### DI-03: Foreign Key Constraints

```sql
-- Entity association (cascade delete)
FOREIGN KEY (entity_id) REFERENCES entities(id) ON DELETE CASCADE
```

### DI-04: Performance Indexes

```sql
-- Query imports by entity and status
INDEX (entity_id, status)

-- Query imports by date
INDEX (import_date)
```

## Import Processing Workflow

### WF-01: File Upload Processing

#### Scenario: CSV File Processing

```
GIVEN a user uploads "transactions.csv"
THEN the system creates an import batch with:
  - status = "Processing"
  - file_name = "transactions.csv"
  - row_count = 0

WHEN file processing begins
THEN each valid row creates a transaction
AND transaction.import_id = batch.id
AND batch.row_count increments

WHEN processing completes without errors
THEN batch.status = "Imported"
AND batch.row_count = final transaction count
```

### WF-02: API Import Processing

#### Scenario: Mercury API Import

```
GIVEN a Mercury API import is initiated
THEN the system creates an import batch with:
  - import_type = "MercuryAPI"
  - file_name = NULL
  - status = "Processing"

WHEN API calls succeed and return transactions
THEN transactions are created with import association
AND batch statistics are updated

WHEN API calls fail
THEN batch.status = "Failed"
AND batch.error_message contains API error details
```

### WF-03: Error Recovery

#### Scenario: Retry Failed Import

```
GIVEN an import batch with status = "Failed"
WHEN the user initiates retry
THEN a new import batch is created (not reused)
AND the failed batch remains for audit trail
AND retry processing uses original source data
```

## Import Analytics

### IA-01: Import Statistics

#### Scenario: Import Success Rate

```
GIVEN multiple import batches exist
WHEN querying import statistics
THEN the system provides:
  - Total imports attempted
  - Successful imports (status = "Imported")
  - Failed imports (status = "Failed")  
  - Duplicate imports (status = "Duplicate")
  - Success rate percentage
```

#### Scenario: Entity Import History

```
GIVEN an entity with multiple imports over time
WHEN viewing entity import history
THEN imports are listed chronologically
AND each entry shows status, date, row count, and type
AND failed imports show error summaries
```

### IA-02: Transaction Provenance

#### Scenario: Transaction Source Tracking

```
GIVEN a transaction created via import
WHEN viewing transaction details
THEN the source import is displayed
AND import metadata (file name, date) is shown
AND users can navigate to the full import batch
```

## Error Handling

### EH-01: File Processing Errors

- **Unreadable File** - "File format not supported or corrupted"
- **Missing Required Columns** - "CSV missing required columns: date, description, amount"
- **Invalid File Size** - "File too large (max 10MB)"

### EH-02: Data Validation Errors

- **Invalid Account** - "Account 'AccountName' not found in entity"
- **Invalid Date Format** - "Date format must be YYYY-MM-DD"
- **Invalid Amount** - "Amount must be a valid number"
- **Future Date** - "Transaction date cannot be in the future"

### EH-03: API Import Errors

- **API Connection Failed** - "Unable to connect to Mercury API"
- **Authentication Failed** - "API credentials are invalid or expired"  
- **Rate Limit Exceeded** - "API rate limit exceeded, retry after [time]"
- **API Data Invalid** - "API returned invalid transaction data"

### EH-04: System Errors

- **Database Error** - "Database error during import processing"
- **Disk Space** - "Insufficient disk space for file processing"
- **Memory Limit** - "File too large to process in available memory"