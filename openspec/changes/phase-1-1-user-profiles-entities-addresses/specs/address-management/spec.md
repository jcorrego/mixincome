# Specification: Address Management

Address management enables users to create and reuse addresses across multiple models (UserProfile, Entity, Account, Asset) within their jurisdiction.

## ADDED Requirements

### Requirement: Create Address

A user SHALL be able to create an address and associate it with a model (UserProfile, Entity, Account, or Asset).

**Details**:
- Address is polymorphic (morphTo): can belong to UserProfile, Entity, Account, Asset
- All addresses are owned by a User (user_id) for authorization/privacy
- Fields: street, city, state, postal_code, country (required), timestamps
- No "type" field — each model has one "official" address
- Address can be created standalone and later reused

#### Scenario: Create address for UserProfile
- **WHEN** User creates address "Calle Mayor 1, Madrid, Spain" for their Spain UserProfile
- **THEN** Address is created with addressable_type='App\Models\UserProfile', addressable_id=1, user_id=1

#### Scenario: Create address for Entity
- **WHEN** User creates address for their LLC
- **THEN** Address is created with addressable_type='App\Models\Entity', addressable_id=2, user_id=1

#### Scenario: Required fields
- **WHEN** User tries to create address without country
- **THEN** Validation fails; country is required

---

### Requirement: Reuse Address

A user SHALL be able to reuse the same address for multiple models (e.g., UserProfile and Entity share same address).

**Details**:
- Same Address row can be referenced by multiple models (via polymorphic relationship)
- Avoids data duplication
- User_id ensures all references belong to same user

#### Scenario: Reuse address between profile and entity
- **WHEN** User creates LLC with same address as their UserProfile
- **THEN** Both UserProfile and Entity reference the same Address row (same address_id)

#### Scenario: Query all addresses for user
- **WHEN** User.addresses is queried (via polymorphic inverse relation)
- **THEN** Returns all addresses owned by that user (no matter if profile, entity, account, etc.)

---

### Requirement: Retrieve Address

A user SHALL be able to retrieve address details and list addresses.

#### Scenario: Get address by ID
- **WHEN** Address ID is queried
- **THEN** Returns Address with all fields: street, city, state, postal_code, country, addressable_id, addressable_type, user_id

#### Scenario: Get model's address
- **WHEN** UserProfile.address is accessed
- **THEN** Returns the associated Address (morphOne relationship)

#### Scenario: List user's addresses
- **WHEN** Querying all addresses for a user
- **THEN** Returns collection of all Address rows with user_id=X (used by any model type)

---

### Requirement: Update Address

A user SHALL be able to update address fields after creation.

#### Scenario: Update street address
- **WHEN** User updates address street from "Calle Mayor 1" to "Calle Mayor 2"
- **THEN** Address.street changes; timestamps updated

#### Scenario: Update all fields
- **WHEN** User updates city, state, postal_code, country
- **THEN** All fields change; no duplicate creation

---

### Requirement: Delete Address

A user SHALL be able to delete an address (soft-delete or hard-delete per implementation).

**Note**: Deletion impacts all models referencing that address. Consider implications before deletion.

#### Scenario: Delete shared address
- **WHEN** User deletes an address used by both UserProfile and Entity
- **THEN** Address is deleted; both models lose their address reference (or show null)

#### Scenario: Cleanup after entity deletion
- **WHEN** Entity is deleted
- **THEN** Related Address MAY be deleted if not shared; behavior TBD (see Open Questions)

---

### Requirement: Polymorphic Association

Address SHALL support polymorphic association with UserProfile, Entity, Account, and Asset.

#### Scenario: Morphable models
- **WHEN** Address is created with addressable_type
- **THEN** addressable_type can be: 'App\Models\UserProfile', 'App\Models\Entity', 'App\Models\Account', 'App\Models\Asset' (Fase 2+)

#### Scenario: Access polymorphic model
- **WHEN** Address.addressable is accessed
- **THEN** Returns the actual model (UserProfile, Entity, etc.) — type preserved

---

### Requirement: User Ownership

All addresses SHALL be owned by a User (user_id field) to enforce authorization boundaries.

#### Scenario: Address owner isolation
- **WHEN** User 1's address is queried
- **THEN** User 2 cannot access it (authorization check in controller, not DB-enforced in Phase 1)

#### Scenario: Cascade delete on user deletion
- **WHEN** User is deleted (if ever)
- **THEN** All addresses with that user_id are deleted

---

### Requirement: One Address Per Model (Phase 1.1)

In Phase 1.1, each model (UserProfile, Entity) SHALL have at most one address.

**Note**: This is not enforced at DB level (no unique constraint yet); enforced by app logic (UI will have 1 address field per model).

#### Scenario: Profile with one address
- **WHEN** UserProfile is created
- **THEN** Profile can have 0 or 1 address (not 2)

#### Scenario: Later phases (Fase 2+)
- **WHEN** Accounts and Assets are added
- **THEN** Each also has 0 or 1 address; same polymorphic table supports all

---

### Requirement: Address Fields Validation

Address fields SHALL be validated before persistence.

#### Scenario: Country required
- **WHEN** User tries to save address without country
- **THEN** Validation fails

#### Scenario: Valid country format
- **WHEN** Address country is provided
- **THEN** Accept ISO code (e.g., "ES", "US", "CO") or full name (validation rules TBD)

---

## Capabilities Enabled by These Requirements

- UserProfile can have address (fiscal address)
- Entity can have address (registered office)
- Future Accounts/Assets can have addresses (Fase 2)
- Address reuse prevents duplication across user's models
- Foundation for document associations (receipts, invoices linked to addresses)
