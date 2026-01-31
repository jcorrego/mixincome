# Specification: Entity Management

Entity management enables users to create and manage additional legal entities (LLC, S-Corp, Corporation, Partnership, Trust) under their tax profiles.

## ADDED Requirements

### Requirement: Entity CRUD Operations

The system SHALL provide a complete CRUD interface for managing legal entities (Entity). A user can create, read, update, and delete entities. Each entity must belong to a user profile.

#### Scenario: List all user entities
- **WHEN** user navigates to `/management/entities`
- **THEN** system displays a table of all entities for the current user (across all profiles), showing: user_profile_id (with profile context), name, entity_type, tax_id, status, created_at

#### Scenario: Create a new entity
- **WHEN** user clicks "Create Entity" and submits the form with: user_profile_id (required, dropdown), name (required, string), entity_type (required, enum: LLC, SCorp, CCorp, Partnership, Trust, Other), tax_id (required, string), status (optional, defaults to "Active")
- **THEN** system validates the form (profile exists, entity_type is valid, tax_id is unique per entity), creates the entity, displays success message, updates the entity list

#### Scenario: Create entity fails on invalid data
- **WHEN** user submits entity creation form with missing user_profile_id, invalid entity_type, or missing name
- **THEN** system displays form validation errors (custom messages for each field) and does not create the entity

#### Scenario: Edit an existing entity
- **WHEN** user clicks "Edit" on an entity and submits updated data (user_profile_id, name, entity_type, tax_id, status)
- **THEN** system validates the form, updates the entity, displays success message, refreshes the entity list

#### Scenario: Delete an entity
- **WHEN** user clicks "Delete" on an entity and confirms the deletion
- **THEN** system checks if entity has associated accounts/transactions/filings; if yes, displays error "Cannot delete entity with related data"; if no, deletes the entity, displays success message, refreshes the entity list

---

### Requirement: Address Association for Entities

When creating or editing an entity, the user SHALL select an address (existing or create new). Each entity can have one address. The address dropdown SHALL display each address using its `display_label` accessor (format: "street, city (country)").

#### Scenario: Create entity with existing address
- **WHEN** user creates an entity and selects an existing address from the "Address" dropdown
- **THEN** the dropdown SHALL display each address as "street, city (country)"
- **THEN** system associates the address to the entity (sets address_id), creates entity successfully

#### Scenario: Create entity without address
- **WHEN** user creates an entity and leaves the "Address" dropdown empty
- **THEN** system creates the entity with address_id = NULL (address is optional)

#### Scenario: Navigate to address management from entity form
- **WHEN** user clicks "Create new address" link in the entity form
- **THEN** system navigates to `/management/addresses` page (no entity creation happens, form is abandoned)

#### Scenario: Edit entity and change address
- **WHEN** user edits an entity and changes the address (selects a different address from dropdown)
- **THEN** the dropdown SHALL display each address as "street, city (country)"
- **THEN** system updates the entity.address_id to the new address, displays success message

---

### Requirement: User Profile Selection for Entity

When creating an entity, the user MUST select an existing user profile. The dropdown SHALL show all active profiles for the current user.

#### Scenario: Create entity shows available profiles
- **WHEN** user clicks "Create Entity" and views the "User Profile" dropdown
- **THEN** system displays all active profiles for the current user (with jurisdiction and tax_id for context)

#### Scenario: Create entity requires profile selection
- **WHEN** user submits entity creation form without selecting a profile
- **THEN** system displays validation error: "User profile is required" and does not create the entity

---

### Requirement: Authorization for Entity Management

Users SHALL only be able to view, edit, and delete entities they own (via their profile). System SHALL enforce this via Authorization Policies.

#### Scenario: User cannot view another user's entity
- **WHEN** user attempts to access/edit/delete an entity belonging to another user's profile
- **THEN** system returns 403 Forbidden

#### Scenario: User can view own entity
- **WHEN** authenticated user lists entities or edits an entity they own
- **THEN** system displays the entity successfully

---

### Requirement: Dependency on User Profiles

If no user profiles exist, the Entity management page SHALL display a warning and disable entity creation.

#### Scenario: Entity page warns when no profiles exist
- **WHEN** user navigates to `/management/entities` and no profiles exist
- **THEN** system displays: "No tax profiles exist. [Link: Create a UserProfile] Create a profile first to add entities." and "Create Entity" button is disabled

#### Scenario: Entity creation enabled when profiles exist
- **WHEN** at least one profile exists
- **THEN** system enables the "Create Entity" button and allows entity creation

---

## Capabilities Enabled by These Requirements

- Accounts and Assets can hang off Entities (Fase 2)
- Documents can be associated with Entities (Fase 4)
- Tax reporting can target specific entities (Fase 3)
- Address system can attach addresses to Entities (foreign key)
