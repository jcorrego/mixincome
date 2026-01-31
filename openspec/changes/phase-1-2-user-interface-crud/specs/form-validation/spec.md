# Specification: Form Validation (MODIFIED)

## MODIFIED Requirements

### Requirement: Form Request Validation Classes
The system SHALL use Laravel Form Request classes for validation instead of inline validation in controllers. This applies to all CRUD operations (create, update).

Form Requests SHALL:
- Define validation rules in `rules()` method
- Define custom error messages in `messages()` method
- Include authorization gate in `authorize()` method (can user perform this action?)
- Have explicit return type hints: `public function validated(): array`

#### Scenario: Jurisdiction validation migrated to Form Request
- **WHEN** user submits the jurisdiction creation form (existing feature, being refactored)
- **THEN** system uses `StoreJurisdictionRequest` class for validation (migrated from inline component validation)
- **THEN** validation rules, custom messages, and authorization gate live in the Form Request class

#### Scenario: UserProfile validation via Form Request
- **WHEN** user creates/updates a UserProfile
- **THEN** system uses `StoreUserProfileRequest` or `UpdateUserProfileRequest` class
- **THEN** validation rules: user_id (authenticated), jurisdiction_id (exists), tax_id (required, unique per user+jurisdiction)
- **THEN** authorization: only authenticated users can create; only owner can update

#### Scenario: Entity validation via Form Request
- **WHEN** user creates/updates an Entity
- **THEN** system uses `StoreEntityRequest` or `UpdateEntityRequest` class
- **THEN** validation rules: user_profile_id (exists), name (required, string), entity_type (required, valid EntityType), tax_id (required, string)
- **THEN** authorization: only authenticated users can create; only owner can update

#### Scenario: Address validation via Form Request
- **WHEN** user creates/updates an Address
- **THEN** system uses `StoreAddressRequest` or `UpdateAddressRequest` class
- **THEN** validation rules: street, city, state, postal_code, country (all required, string)
- **THEN** authorization: only authenticated users can create; only owner can update

#### Scenario: Validation error display
- **WHEN** form validation fails
- **THEN** system displays custom error messages from the Form Request (not generic Laravel messages)
- **THEN** Livewire component receives validation errors and displays them in the form

#### Scenario: Form Request reusable for API and UI
- **WHEN** a Livewire component dispatches data to an API endpoint (POST/PATCH)
- **AND** when a potential future API client sends the same data
- **THEN** both use the same Form Request class, ensuring consistent validation

---

### Requirement: Custom Error Messages
Form Requests SHALL provide custom, user-friendly error messages for all validation rules.

#### Scenario: Custom message for required field
- **WHEN** user submits a form missing a required field (e.g., jurisdiction_id)
- **THEN** system displays: "Jurisdiction is required" (not "The jurisdiction_id field is required")

#### Scenario: Custom message for unique constraint
- **WHEN** user submits a profile with a tax_id that already exists for this user+jurisdiction combination
- **THEN** system displays: "A tax profile for this jurisdiction already exists with this tax ID" (not "The tax_id has already been taken")

#### Scenario: Custom message for enum validation
- **WHEN** user submits an entity with invalid entity_type
- **THEN** system displays: "Entity type must be one of: LLC, SCorp, CCorp, Partnership, Trust, Other" (not "The entity_type field is invalid")

---

### Requirement: Authorization Gate in Form Requests
Form Requests SHALL include an `authorize()` method that explicitly checks if the user can perform the action.

#### Scenario: Non-authenticated user cannot create
- **WHEN** an unauthenticated request is sent to create a UserProfile/Entity/Address
- **THEN** Form Request's `authorize()` returns false
- **THEN** system responds with 403 Forbidden (Livewire redirects to login, API returns 403)

#### Scenario: Owner-only authorization for updates
- **WHEN** `UpdateUserProfileRequest::authorize()` is called
- **THEN** it checks: does the authenticated user own this UserProfile? (user_id match)
- **THEN** if not, returns false, system responds with 403 Forbidden

#### Scenario: Authorization check prevents unauthorized access
- **WHEN** user A attempts to update user B's profile
- **THEN** Form Request's `authorize()` returns false
- **THEN** system prevents the update and responds with 403 Forbidden
