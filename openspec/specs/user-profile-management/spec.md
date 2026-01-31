# Specification: User Profile Management

User profile management enables users to establish and manage their legal tax identity in multiple jurisdictions.

## ADDED Requirements

### Requirement: User Profile CRUD Operations

The system SHALL provide a complete CRUD interface for managing tax profiles (UserProfile). A user can create, read, update, and delete their own profiles. Each profile is tied to a jurisdiction.

#### Scenario: List all user profiles
- **WHEN** user navigates to `/management/profiles`
- **THEN** system displays a table of all profiles for the current user, grouped by jurisdiction, showing: jurisdiction name, tax_id, status, created_at

#### Scenario: Create a new profile
- **WHEN** user clicks "Create Profile" and submits the form with: jurisdiction (required, dropdown), tax_id (required, string), status (optional, defaults to "Active")
- **THEN** system validates the form (jurisdiction exists, tax_id is unique per user+jurisdiction), creates the profile, displays success message, updates the profile list

#### Scenario: Create profile fails on invalid data
- **WHEN** user submits profile creation form with missing jurisdiction or duplicate tax_id
- **THEN** system displays form validation errors (custom messages for each field) and does not create the profile

#### Scenario: Edit an existing profile
- **WHEN** user clicks "Edit" on a profile and submits updated data (jurisdiction, tax_id, status)
- **THEN** system validates the form, updates the profile, displays success message, refreshes the profile list

#### Scenario: Delete a profile
- **WHEN** user clicks "Delete" on a profile and confirms the deletion
- **THEN** system checks if profile has associated entities; if yes, displays error "Cannot delete profile with entities"; if no, deletes the profile, displays success message, refreshes the profile list

---

### Requirement: Address Association for Profiles

When creating or editing a profile, the user SHALL select an address (existing or create new). Each profile can have one address. The address dropdown SHALL display each address using its `display_label` accessor (format: "street, city (country)").

#### Scenario: Create profile with existing address
- **WHEN** user creates a profile and selects an existing address from the "Address" dropdown
- **THEN** the dropdown SHALL display each address as "street, city (country)"
- **THEN** system associates the address to the profile (sets address_id), creates profile successfully

#### Scenario: Create profile without address
- **WHEN** user creates a profile and leaves the "Address" dropdown empty
- **THEN** system creates the profile with address_id = NULL (address is optional)

#### Scenario: Navigate to address management
- **WHEN** user clicks "Create new address" link in the profile form
- **THEN** system navigates to `/management/addresses` page (no profile creation happens, form is abandoned)

#### Scenario: Edit profile and change address
- **WHEN** user edits a profile and changes the address (selects a different address from dropdown)
- **THEN** the dropdown SHALL display each address as "street, city (country)"
- **THEN** system updates the profile.address_id to the new address, displays success message

---

### Requirement: Authorization for Profile Management

Users SHALL only be able to view, edit, and delete their own profiles. System SHALL enforce this via Authorization Policies.

#### Scenario: User cannot view another user's profile
- **WHEN** user attempts to access/edit/delete a profile owned by another user (different user_id)
- **THEN** system returns 403 Forbidden

#### Scenario: User can view own profile
- **WHEN** authenticated user lists profiles or views a profile they own
- **THEN** system displays the profile successfully

---

### Requirement: Dependency Checks

If the system has no profiles, the Entity management page SHALL display a warning message and disable the "Create Entity" button.

#### Scenario: Entity page warns when no profiles exist
- **WHEN** user navigates to `/management/entities` and no profiles exist for this user
- **THEN** system displays: "No tax profiles exist. [Link: Create a UserProfile] Create a profile first to add entities."

#### Scenario: Entity page allows creation when profiles exist
- **WHEN** user navigates to `/management/entities` and at least one profile exists
- **THEN** system displays the entity list normally and the "Create Entity" button is enabled

---

## Capabilities Enabled by These Requirements

- Entity management can reference a specific UserProfile
- Address system can associate addresses to UserProfiles (foreign key)
- Future Fase 2 (Accounts, Transactions) can hang off UserProfile
- Future phases can organize by UserProfile
