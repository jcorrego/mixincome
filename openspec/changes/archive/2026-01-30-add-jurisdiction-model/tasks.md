# Implementation Tasks: Jurisdiction Model

## 1. Database Layer

- [x] 1.1 Create migration `create_jurisdictions_table` with fields: id, name, iso_code (3-char unique), timezone, default_currency (3-char), timestamps
- [x] 1.2 Create JurisdictionFactory with realistic faker data
- [x] 1.3 Create JurisdictionSeeder with upsert for ES, US, CO jurisdictions

## 2. Model Layer

- [x] 2.1 Create Jurisdiction model with declare(strict_types=1)
- [x] 2.2 Add fillable attributes and PHPDoc
- [x] 2.3 Add relationship method stubs: userProfiles(), entities() (return HasMany with proper types)

## 3. Routes

- [x] 3.1 Create routes/management.php with auth middleware
- [x] 3.2 Add route for management.jurisdictions view
- [x] 3.3 Register management.php in bootstrap/app.php if needed

## 4. Livewire Component

- [x] 4.1 Create Livewire component Management/Jurisdictions
- [x] 4.2 Implement table view with all jurisdictions
- [x] 4.3 Implement create form with validation rules
- [x] 4.4 Implement edit form
- [x] 4.5 Implement delete with dependency check (soft validation)

## 5. Views

- [x] 5.1 Create management.jurisdictions Blade view
- [x] 5.2 Use Flux UI components for table and forms
- [x] 5.3 Add navigation link in app sidebar/menu

## 6. Tests - Feature

- [x] 6.1 Create tests/Feature/Management/JurisdictionTest.php
- [x] 6.2 Write auth tests (view page, redirect unauth)
- [x] 6.3 Write create tests (valid, duplicate, invalid length, missing fields)
- [x] 6.4 Write update tests (valid, duplicate iso_code, invalid timezone)
- [x] 6.5 Write delete tests (no dependencies)

## 7. Tests - Unit

- [x] 7.1 Create tests/Unit/Models/JurisdictionTest.php
- [x] 7.2 Write factory validation test
- [x] 7.3 Write fillable attributes test
- [x] 7.4 Write unique constraint test

## 8. Tests - Seeder

- [x] 8.1 Create tests/Unit/Seeders/JurisdictionSeederTest.php
- [x] 8.2 Write seeder creates 3 jurisdictions test
- [x] 8.3 Write seeder idempotent test
- [x] 8.4 Write seeder data validation tests (ES, US, CO)

## 9. Quality & Finalization

- [x] 9.1 Run migration and seeder locally
- [x] 9.2 Run all tests: php artisan test --compact
- [x] 9.3 Run linter: vendor/bin/pint
- [x] 9.4 Run static analysis: vendor/bin/phpstan
- [x] 9.5 Verify type coverage: composer test
- [x] 9.6 Test UI manually at /management/jurisdictions
