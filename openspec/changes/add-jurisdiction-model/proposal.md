# Proposal: Add Jurisdiction Model

## Why

MixIncome needs to support multi-jurisdiction tax reporting for Spain, USA, and Colombia. Each jurisdiction has different default currencies, timezones, and needs proper identification via ISO codes.

The Jurisdiction model is the foundation for:
- UserProfiles (tax profiles per jurisdiction)
- Entities (companies/individuals registered in jurisdictions)
- TransactionCategories (tax categories are jurisdiction-specific)
- TaxYears and Filings (tax reporting per jurisdiction)

This is the first model to migrate from Velor and serves as the template for all subsequent model migrations.

## What Changes

### Database
- **Migration**: `create_jurisdictions_table` with fields:
  - `id`, `name`, `iso_code` (3-char, unique)
  - `timezone`, `default_currency` (3-char)
  - `timestamps`

### Model
- **Eloquent Model**: `App\Models\Jurisdiction`
  - Mass assignable attributes
  - Relationships: `userProfiles()`, `entities()` (will be added as those models are created)
  - Return type hints on all methods
  - `declare(strict_types=1)` for strict mode
  - PHPDoc blocks for better type coverage

### Seeder
- **JurisdictionSeeder**: Pre-populate Spain, USA, Colombia:
  - Spain: ES, Europe/Madrid, EUR
  - USA: US, America/New_York, USD
  - Colombia: CO, America/Bogota, COP

### Factory
- **JurisdictionFactory**: For testing with realistic data

### Admin UI
- **Livewire Component**: `Management/Jurisdictions`
  - List all jurisdictions in a table
  - Create/edit/delete (soft validation - can't delete if has dependencies)
  - Uses Flux UI components
  - Following existing `Management` pattern

### Routes
- Add to `routes/management.php` (or create if doesn't exist):
  - `GET /management/jurisdictions` → `management.jurisdictions` view

### Tests
- **Feature tests**:
  - Create jurisdiction
  - Update jurisdiction
  - Delete jurisdiction (blocks if has relationships)
  - Unique iso_code constraint
  - Seeder populates correct data
- **Unit tests**:
  - Relationship methods return correct types
  - Factory generates valid data

## Capabilities

### New
- `jurisdiction-management`: CRUD operations for jurisdictions via admin UI

### Modified
- None (this is the first domain model)

## Impact

- **Breaking**: No (new functionality)
- **Dependencies**: None (foundational model)
- **Future models blocked by this**: UserProfile, Entity, TransactionCategory, TaxYear
- **Database**: Adds 1 new table, seeds 3 jurisdictions
- **UI**: Adds new management section (will create pattern for other admin views)
