# Specification: Address Management

Address management enables users to create and reuse addresses across multiple models (UserProfile, Entity, Account, Asset) within their jurisdiction.

## ADDED Requirements

### Requirement: Address CRUD Operations (Independent)

The system SHALL provide a complete CRUD interface for managing addresses (Address). Addresses are independent entities that can be reused across UserProfiles, Entities, Accounts, and Assets. A user can create, read, update, and delete addresses.

#### Scenario: List all user addresses
- **WHEN** user navigates to `/management/addresses`
- **THEN** system displays a table of all addresses for the current user (owned by user_id), showing: street, city, state, postal_code, country (displayed as full country name from Country enum), association_status (text or badge), created_at

#### Scenario: Address association status
- **WHEN** user views the address list
- **THEN** for each address, system displays the association status:
  - "Associated to: UserProfile #5, Entity #3" (if used by multiple models)
  - "Associated to: UserProfile #1" (if used by one model)
  - "(Unassociated)" (if not used by any model yet)

#### Scenario: Create a new address
- **WHEN** user clicks "Create Address" and submits the form with: street (required, string), city (required, string), state (required, string), postal_code (required, string), country (required, valid Country enum value — selected from searchable dropdown)
- **THEN** system validates the form (all fields required, country must be a valid ISO alpha-2 code), creates the address with user_id = current user, displays success message, updates the address list

#### Scenario: Country field is a searchable dropdown
- **WHEN** user opens the address create or edit form
- **THEN** the country field SHALL be a searchable `flux:select` dropdown listing all countries from the Country enum
- **AND** the dropdown SHALL show country names as labels and ISO alpha-2 codes as values
- **AND** the user's jurisdiction-relevant countries (US, ES, CO) SHALL appear at the top of the list

#### Scenario: Create address fails on invalid country
- **WHEN** user submits address creation form with a country value that is not a valid Country enum code
- **THEN** system displays validation error for the country field and does not create the address

#### Scenario: Create address fails on invalid data
- **WHEN** user submits address creation form with missing required fields
- **THEN** system displays form validation errors (custom messages for each field) and does not create the address

#### Scenario: Edit an existing address
- **WHEN** user clicks "Edit" on an address and submits updated data (street, city, state, postal_code, country)
- **THEN** system validates the form, updates the address, displays success message, refreshes the address list

#### Scenario: Delete an address
- **WHEN** user clicks "Delete" on an address and confirms the deletion
- **THEN** system checks if address is associated to any models (user_profile_id, entity_id, account_id, asset_id NOT NULL); if yes, displays error "Cannot delete address in use" with model details; if no, deletes the address, displays success message, refreshes the address list

---

### Requirement: Address Reusability

The same address resource SHALL be selectable and usable across multiple models (UserProfile, Entity, Account, Asset). Users can create one address and assign it to multiple models.

#### Scenario: Address used by multiple models
- **WHEN** address "123 Main St" has address_id = 5, and both UserProfile #1 and Entity #2 have address_id = 5
- **THEN** system displays in the address list: "Associated to: UserProfile #1, Entity #2"

#### Scenario: Address can be reassigned
- **WHEN** user edits UserProfile #1 and changes the address to address_id = 6
- **THEN** the old address (5) is no longer associated to UserProfile #1, but still associated to Entity #2
- **THEN** when user deletes address #5 now, system allows deletion because it's only used by Entity #2... wait, no. If Entity #2 still uses it, deletion is blocked.

#### Scenario: Prevent deletion of in-use address
- **WHEN** address #5 is assigned to Entity #2 (entity.address_id = 5)
- **AND** user attempts to delete address #5
- **THEN** system displays error: "Cannot delete address. In use by: Entity 'My LLC' (#2)" and prevents deletion

---

### Requirement: Address Ownership

Each address has an owner (user_id) who created it. Users SHALL only be able to view, edit, and delete their own addresses.

#### Scenario: User can manage own addresses
- **WHEN** user navigates to `/management/addresses`
- **THEN** system displays only addresses where user_id = current user's id

#### Scenario: User cannot view another user's address
- **WHEN** user attempts to access/edit/delete an address owned by another user
- **THEN** system returns 403 Forbidden

---

### Requirement: Address Structure (FK-based, not Polymorphic)

Address table SHALL be an independent resource with `user_id` (for ownership). Other models (UserProfile, Entity, Account, Asset) have `address_id` FK pointing to addresses. Each model can have one address, but an address can be used by multiple models.

#### Scenario: Address structure validation
- **WHEN** system stores an address with user_id = 3, street = "123 Main St", city = "New York"
- **THEN** the address is valid and owned by User #3
- **WHEN** UserProfile #5 has address_id = 10 pointing to Address #10
- **THEN** UserProfile #5 is associated to Address #10

#### Scenario: Address reusability across model types
- **WHEN** multiple models (UserProfile, Entity) have address_id = 5
- **THEN** they all reference the same Address #5 (no duplication)

---

### Requirement: Address Model Country Enum Cast

The Address model SHALL cast the `country` attribute to the `Country` enum and provide a `display_label` accessor.

#### Scenario: Country attribute is cast to enum
- **WHEN** an Address is retrieved from the database with country = 'US'
- **THEN** `$address->country` SHALL return `Country::UnitedStates` enum instance

#### Scenario: Display label includes country name
- **WHEN** an Address has street = "123 Main St", city = "Miami", country = Country::UnitedStates
- **THEN** `$address->display_label` SHALL return `"123 Main St, Miami (United States)"`

---

### Requirement: Address Display in Profile/Entity Forms

When users create or edit a UserProfile or Entity, they can select an existing address from a dropdown. System SHALL display available addresses with the country name included.

#### Scenario: Address dropdown shows display label with country
- **WHEN** user opens the profile/entity creation form and views the "Address" dropdown
- **THEN** system displays each address using its `display_label` format: `"{street}, {city} ({country_name})"`
- **AND** system displays: "(None)", then all available addresses

#### Scenario: User can leave address empty
- **WHEN** user submits a profile/entity creation form without selecting an address
- **THEN** system creates the model with address_id = NULL

---

### Requirement: Address Validation Rules

The address form requests SHALL validate the country field against the Country enum.

#### Scenario: Store address validates country as enum
- **WHEN** StoreAddressRequest validates the country field
- **THEN** it SHALL use the `Enum:App\Enums\Country` rule (or `Rule::enum(Country::class)`)
- **AND** reject any value not present in the Country enum

#### Scenario: Update address validates country as enum
- **WHEN** UpdateAddressRequest validates the country field
- **THEN** it SHALL use the same Country enum validation rule

---

## Capabilities Enabled by These Requirements

- UserProfile can have address (fiscal address)
- Entity can have address (registered office)
- Future Accounts/Assets can have addresses (Fase 2)
- Address reuse prevents duplication across user's models
- Foundation for document associations (receipts, invoices linked to addresses)
