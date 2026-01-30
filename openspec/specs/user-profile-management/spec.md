# Specification: User Profile Management

User profile management enables users to establish and manage their legal tax identity in multiple jurisdictions.

## ADDED Requirements

### Requirement: Create User Profile

A user SHALL be able to create a tax profile for a jurisdiction, establishing their legal identity in that country.

**Details**:
- One UserProfile per (user, jurisdiction) pair — no duplicates
- Requires user_id, jurisdiction_id, and tax_id (jurisdiction-specific identifier like SSN, RUT, NIF)
- Status defaults to 'Active'
- Timestamps (created_at, updated_at) auto-managed

#### Scenario: Create first profile for Spain
- **WHEN** User "Juan" creates a profile for Spain with tax_id="NIF123456789"
- **THEN** UserProfile is created with user_id=1, jurisdiction_id=2, tax_id="NIF123456789", status="Active"

#### Scenario: Prevent duplicate profile
- **WHEN** User tries to create a second profile for Spain (same user, same jurisdiction)
- **THEN** Validation fails with unique constraint error

#### Scenario: Create second profile for different jurisdiction
- **WHEN** User "Juan" creates a profile for USA with tax_id="SSN987654321"
- **THEN** Both profiles coexist; user now has 2 UserProfiles

---

### Requirement: Retrieve User Profile

A user or admin SHALL be able to retrieve their profile details (or all profiles for a user).

#### Scenario: Get single profile by ID
- **WHEN** Profile ID is queried
- **THEN** Returns UserProfile with all fields: user_id, jurisdiction_id, tax_id, status, timestamps

#### Scenario: List all profiles for user
- **WHEN** User ID is queried
- **THEN** Returns collection of all UserProfiles for that user, with eager-loaded jurisdiction

#### Scenario: Get profile with related entities
- **WHEN** Profile is loaded with entities
- **THEN** Returns profile with entities array (no N+1 queries)

---

### Requirement: Update User Profile Tax ID

A user SHALL be able to update their tax_id if it changes (e.g., new SSN, name change).

**Note**: Update implies replacement, not append. Only one tax_id per profile.

#### Scenario: Update tax_id
- **WHEN** User updates their profile tax_id to "NIF999999999"
- **THEN** Profile.tax_id changes; timestamps updated; no duplicate profiles created

#### Scenario: Update status
- **WHEN** User changes profile status from "Active" to "Inactive"
- **THEN** Status changes; related entities inherit no status impact (independent)

---

### Requirement: Delete User Profile

A user SHALL be able to delete their profile (cascade deletes related entities).

**Implications**: Deleting a profile cascades to all related entities and their data.

#### Scenario: Delete profile with entities
- **WHEN** User deletes a UserProfile that has 2 related Entities
- **THEN** Profile + all 2 Entities are deleted; no orphans remain

#### Scenario: Delete profile without entities
- **WHEN** User deletes a UserProfile with no entities
- **THEN** Profile is deleted cleanly

---

### Requirement: Profile-Jurisdiction Relationship

A UserProfile SHALL maintain a relationship with exactly one Jurisdiction (cannot change after creation).

#### Scenario: Access profile's jurisdiction
- **WHEN** Profile.jurisdiction is accessed
- **THEN** Returns Jurisdiction model with code, name, default_currency, timezone

#### Scenario: Default currency from jurisdiction
- **WHEN** Determining user's base currency for a profile
- **THEN** Use Profile.jurisdiction.default_currency (not a separate profile field)

---

### Requirement: Unique (user_id, jurisdiction_id) Constraint

The database SHALL enforce uniqueness at (user_id, jurisdiction_id) level.

#### Scenario: Constraint enforced at migration
- **WHEN** Migration runs
- **THEN** Unique index exists on (user_id, jurisdiction_id)

---

## Capabilities Enabled by These Requirements

- Entity management can reference a specific UserProfile
- Address system can associate addresses to UserProfiles (polymorphic)
- Future Fase 2 (Accounts, Transactions) can hang off UserProfile
- Future Fase 1.2 (TaxYear) can organize by UserProfile
