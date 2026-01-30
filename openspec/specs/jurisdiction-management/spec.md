# Jurisdiction Management Specification

## ADDED Requirements

### Requirement: View all jurisdictions

The system SHALL display a list of all jurisdictions in the admin UI.

#### Scenario: Authenticated user views jurisdictions page
- **WHEN** An authenticated user navigates to `/management/jurisdictions`
- **THEN** The system displays a table with all jurisdictions showing name, iso_code, timezone, and default_currency

#### Scenario: Unauthenticated user attempts to access
- **WHEN** An unauthenticated user navigates to `/management/jurisdictions`
- **THEN** The system redirects to the login page

### Requirement: Create jurisdiction

The system SHALL allow administrators to create new jurisdictions.

#### Scenario: Create jurisdiction with valid data
- **WHEN** An administrator submits a form with name="Germany", iso_code="DE", timezone="Europe/Berlin", default_currency="EUR"
- **THEN** The system creates the jurisdiction and displays it in the list

#### Scenario: Create jurisdiction with duplicate iso_code
- **WHEN** An administrator attempts to create a jurisdiction with an iso_code that already exists
- **THEN** The system displays a validation error "The iso code has already been taken"

#### Scenario: Create jurisdiction with invalid iso_code length
- **WHEN** An administrator submits iso_code with length != 3
- **THEN** The system displays a validation error "The iso code must be 3 characters"

#### Scenario: Create jurisdiction with missing required fields
- **WHEN** An administrator submits the form without name, iso_code, timezone, or default_currency
- **THEN** The system displays validation errors for all missing required fields

### Requirement: Update jurisdiction

The system SHALL allow administrators to update existing jurisdictions.

#### Scenario: Update jurisdiction with valid data
- **WHEN** An administrator updates a jurisdiction's name from "United States" to "USA"
- **THEN** The system saves the change and displays the updated name

#### Scenario: Update jurisdiction iso_code to duplicate
- **WHEN** An administrator attempts to change iso_code to one that already exists
- **THEN** The system displays a validation error "The iso code has already been taken"

#### Scenario: Update jurisdiction with invalid timezone
- **WHEN** An administrator enters an invalid timezone identifier
- **THEN** The system displays a validation error "The timezone must be a valid timezone identifier"

### Requirement: Delete jurisdiction

The system SHALL allow administrators to delete jurisdictions that have no dependencies.

#### Scenario: Delete jurisdiction with no dependencies
- **WHEN** An administrator deletes a jurisdiction that has no user_profiles, entities, or other related records
- **THEN** The system deletes the jurisdiction and removes it from the list

#### Scenario: Attempt to delete jurisdiction with dependencies
- **WHEN** An administrator attempts to delete a jurisdiction that has related user_profiles or entities
- **THEN** The system prevents deletion and displays an error message "Cannot delete jurisdiction because it has related records"

#### Scenario: Delete non-existent jurisdiction
- **WHEN** An administrator attempts to delete a jurisdiction that doesn't exist
- **THEN** The system displays a 404 error

### Requirement: Seed initial jurisdictions

The system SHALL pre-populate jurisdictions for Spain, USA, and Colombia on initial setup.

#### Scenario: Run seeder on fresh database
- **WHEN** The JurisdictionSeeder is executed on an empty database
- **THEN** The system creates 3 jurisdictions: Spain (ES), United States (US), Colombia (CO) with correct timezone and currency

#### Scenario: Run seeder on database with existing jurisdictions
- **WHEN** The JurisdictionSeeder is executed and jurisdictions already exist
- **THEN** The system updates existing jurisdictions (idempotent upsert) without creating duplicates

### Requirement: Validate jurisdiction data

The system SHALL enforce data validation rules for all jurisdiction operations.

#### Scenario: iso_code must be unique
- **WHEN** Creating or updating a jurisdiction
- **THEN** The system enforces unique constraint on iso_code column

#### Scenario: iso_code must be exactly 3 characters
- **WHEN** Creating or updating a jurisdiction
- **THEN** The system validates iso_code length is exactly 3 characters

#### Scenario: default_currency must be exactly 3 characters
- **WHEN** Creating or updating a jurisdiction
- **THEN** The system validates default_currency length is exactly 3 characters

#### Scenario: All required fields must be present
- **WHEN** Creating a jurisdiction
- **THEN** The system requires name, iso_code, timezone, and default_currency fields

### Requirement: Display jurisdiction relationships

The system SHALL show jurisdiction usage in the UI.

#### Scenario: Jurisdiction with dependencies shows count
- **WHEN** A jurisdiction has 5 entities and 2 user_profiles
- **THEN** The UI displays "Used by: 5 entities, 2 profiles" or similar indicator

#### Scenario: Jurisdiction with no dependencies shows nothing
- **WHEN** A jurisdiction has no related records
- **THEN** The UI shows no dependency indicators
