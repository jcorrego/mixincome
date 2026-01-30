# Specification: Entity Management

Entity management enables users to create and manage additional legal entities (LLC, S-Corp, Corporation, Partnership, Trust) under their tax profiles.

## ADDED Requirements

### Requirement: Create Entity

A user SHALL be able to create a legal entity (non-individual) under one of their UserProfiles.

**Details**:
- Entity belongs to exactly one UserProfile
- entity_type MUST be one of: LLC, SCorp, CCorp, Partnership, Trust, Other (NO "Individual")
- Requires name (company name) and tax_id (EIN, registration number, etc.)
- Status defaults to 'Active'
- Timestamps auto-managed
- Entities are NOT auto-created; user must explicitly create them

#### Scenario: Create LLC under USA profile
- **WHEN** User creates Entity with name="My Tech LLC", entity_type=LLC, tax_id="EIN123456789" under USA profile
- **THEN** Entity is created with user_profile_id=1, entity_type=LLC, status="Active"

#### Scenario: Prevent individual entity
- **WHEN** User tries to create Entity with entity_type="Individual"
- **THEN** Validation fails; enum only accepts LLC, SCorp, CCorp, Partnership, Trust, Other

#### Scenario: Create multiple entities under same profile
- **WHEN** User creates Entity A (LLC) and Entity B (SCorp) under same USA profile
- **THEN** Both entities coexist; profile has 2 entities

#### Scenario: Entity NOT auto-created with profile
- **WHEN** User creates a new UserProfile
- **THEN** No entities are automatically created; profile.entities = []

---

### Requirement: Retrieve Entity

A user SHALL be able to retrieve entity details and list entities under a profile.

#### Scenario: Get entity by ID
- **WHEN** Entity ID is queried
- **THEN** Returns Entity with all fields: user_profile_id, name, entity_type, tax_id, status, timestamps

#### Scenario: List entities for profile
- **WHEN** UserProfile entities are queried
- **THEN** Returns collection of all entities under that profile

#### Scenario: Get entity with profile context
- **WHEN** Entity is loaded with its profile
- **THEN** Returns entity with eager-loaded profile (no N+1)

---

### Requirement: Update Entity

A user SHALL be able to update entity details (name, status, tax_id).

#### Scenario: Update entity name
- **WHEN** User updates Entity name to "New LLC Name"
- **THEN** Entity.name changes; timestamps updated

#### Scenario: Change entity status
- **WHEN** User deactivates entity (status → "Inactive")
- **THEN** Status changes; related accounts/assets remain (independent status)

#### Scenario: Update tax_id
- **WHEN** User updates tax_id after registration change
- **THEN** tax_id is updated; no duplicates created

---

### Requirement: Delete Entity

A user SHALL be able to delete an entity (cascade deletes related accounts, assets, documents).

#### Scenario: Delete entity with accounts
- **WHEN** User deletes Entity with related Accounts
- **THEN** Entity + all related Accounts are deleted

#### Scenario: Delete entity with address
- **WHEN** User deletes Entity that has an Address
- **THEN** Entity is deleted; Address is deleted (or soft-deleted if audit needed later)

---

### Requirement: Entity-UserProfile Relationship

An Entity SHALL belong to exactly one UserProfile and cannot change that relationship.

#### Scenario: Access entity's profile
- **WHEN** Entity.userProfile is accessed
- **THEN** Returns UserProfile model with user_id, jurisdiction_id, tax_id

#### Scenario: Entity inherits jurisdiction via profile
- **WHEN** Determining entity's jurisdiction
- **THEN** Use Entity.userProfile.jurisdiction (entity has no direct jurisdiction field)

---

### Requirement: Entity Tax ID Requirement

An Entity SHALL require a tax_id at creation (cannot be null).

#### Scenario: Create without tax_id
- **WHEN** User tries to create Entity without tax_id
- **THEN** Validation fails; tax_id is required

---

### Requirement: Entity Types (Enum)

The system SHALL support the following entity types: LLC, SCorp, CCorp, Partnership, Trust, Other.

#### Scenario: List available entity types
- **WHEN** Entity.entity_type values are enumerated
- **THEN** Returns: LLC, SCorp, CCorp, Partnership, Trust, Other (no Individual)

#### Scenario: Enum cast in model
- **WHEN** Entity is instantiated with entity_type="LLC"
- **THEN** entity_type is cast to EntityType enum (type-safe)

---

## Capabilities Enabled by These Requirements

- Accounts and Assets can hang off Entities (Fase 2)
- Documents can be associated with Entities (Fase 4)
- Tax reporting can target specific entities (Fase 3)
- Address system can attach addresses to Entities (polymorphic)
