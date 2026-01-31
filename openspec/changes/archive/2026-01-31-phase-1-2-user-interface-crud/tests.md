# Test Plan: Phase 1.2 — User Interface CRUD

## 1. User Profile Management

### 1.1 Profile CRUD Operations

- [ ] 1.1.1 [Feature] User can list all their profiles (Scenario: List all user profiles)
- [ ] 1.1.2 [Feature] Profile list displays jurisdiction, tax_id, status, created_at (Scenario: List all user profiles)
- [ ] 1.1.3 [Feature] User can create a new profile with valid data (Scenario: Create a new profile)
- [ ] 1.1.4 [Feature] Profile creation fails with missing jurisdiction (Scenario: Create profile fails on invalid data)
- [ ] 1.1.5 [Feature] Profile creation fails with duplicate tax_id for user+jurisdiction (Scenario: Create profile fails on invalid data)
- [ ] 1.1.6 [Feature] Profile creation displays validation error messages (Scenario: Create profile fails on invalid data)
- [ ] 1.1.7 [Feature] User can edit an existing profile (Scenario: Edit an existing profile)
- [ ] 1.1.8 [Feature] Profile edit updates all fields (jurisdiction, tax_id, status) (Scenario: Edit an existing profile)
- [ ] 1.1.9 [Feature] User can delete a profile with no entities (Scenario: Delete a profile)
- [ ] 1.1.10 [Feature] Profile deletion is prevented if profile has entities (Scenario: Delete a profile)
- [ ] 1.1.11 [Feature] Profile deletion displays error message when blocked (Scenario: Delete a profile)
- [ ] 1.1.12 [Unit] StoreUserProfileRequest validates jurisdiction exists (Scenario: Create profile fails on invalid data)
- [ ] 1.1.13 [Unit] UpdateUserProfileRequest validates tax_id uniqueness (Scenario: Create profile fails on invalid data)

### 1.2 Address Association for Profiles

- [ ] 1.2.1 [Feature] User can create profile with existing address (Scenario: Create profile with existing address)
- [ ] 1.2.2 [Feature] Profile created with address associates address_id correctly (Scenario: Create profile with existing address)
- [ ] 1.2.3 [Feature] User can create profile without address (address_id = NULL) (Scenario: Create profile without address)
- [ ] 1.2.4 [Feature] Clicking "Create new address" navigates to /management/addresses (Scenario: Navigate to address management)
- [ ] 1.2.5 [Feature] User can edit profile and change address (Scenario: Edit profile and change address)
- [ ] 1.2.6 [Feature] Profile address update reflects in database (Scenario: Edit profile and change address)

### 1.3 Authorization for Profiles

- [ ] 1.3.1 [Feature] Unauthenticated user is redirected when accessing /management/profiles (Scenario: User can view own profile)
- [ ] 1.3.2 [Feature] User cannot view another user's profile (403 Forbidden) (Scenario: User cannot view another user's profile)
- [ ] 1.3.3 [Feature] User cannot edit another user's profile (403 Forbidden) (Scenario: User cannot view another user's profile)
- [ ] 1.3.4 [Feature] User cannot delete another user's profile (403 Forbidden) (Scenario: User cannot view another user's profile)
- [ ] 1.3.5 [Unit] UserProfilePolicy->view() returns true only for profile owner (Scenario: User cannot view another user's profile)
- [ ] 1.3.6 [Unit] UserProfilePolicy->update() returns true only for profile owner (Scenario: User cannot view another user's profile)
- [ ] 1.3.7 [Unit] UserProfilePolicy->delete() returns true only for profile owner (Scenario: User cannot view another user's profile)

### 1.4 Dependency Checks for Profiles

- [ ] 1.4.1 [Feature] Entity page displays warning when no profiles exist (Scenario: Entity page warns when no profiles exist)
- [ ] 1.4.2 [Feature] "Create Entity" button is disabled when no profiles exist (Scenario: Entity page warns when no profiles exist)
- [ ] 1.4.3 [Feature] Warning message includes link to create profile (Scenario: Entity page warns when no profiles exist)
- [ ] 1.4.4 [Feature] "Create Entity" button is enabled when profiles exist (Scenario: Entity page allows creation when profiles exist)
- [ ] 1.4.5 [Feature] Entity list is displayed normally when profiles exist (Scenario: Entity page allows creation when profiles exist)

---

## 2. Entity Management

### 2.1 Entity CRUD Operations

- [ ] 2.1.1 [Feature] User can list all their entities (across all profiles) (Scenario: List all user entities)
- [ ] 2.1.2 [Feature] Entity list displays user_profile context, name, entity_type, tax_id, status, created_at (Scenario: List all user entities)
- [ ] 2.1.3 [Feature] User can create a new entity with valid data (Scenario: Create a new entity)
- [ ] 2.1.4 [Feature] Entity creation requires valid entity_type (Scenario: Create a new entity)
- [ ] 2.1.5 [Feature] Entity creation fails with missing user_profile_id (Scenario: Create entity fails on invalid data)
- [ ] 2.1.6 [Feature] Entity creation fails with invalid entity_type (Scenario: Create entity fails on invalid data)
- [ ] 2.1.7 [Feature] Entity creation fails with missing name (Scenario: Create entity fails on invalid data)
- [ ] 2.1.8 [Feature] Entity creation displays validation error messages (Scenario: Create entity fails on invalid data)
- [ ] 2.1.9 [Feature] User can edit an existing entity (Scenario: Edit an existing entity)
- [ ] 2.1.10 [Feature] Entity edit updates all fields (user_profile_id, name, entity_type, tax_id, status) (Scenario: Edit an existing entity)
- [ ] 2.1.11 [Feature] User can delete an entity with no accounts/transactions/filings (Scenario: Delete an entity)
- [ ] 2.1.12 [Feature] Entity deletion is prevented if entity has related data (Scenario: Delete an entity)
- [ ] 2.1.13 [Feature] Entity deletion displays error message with model details (Scenario: Delete an entity)
- [ ] 2.1.14 [Unit] StoreEntityRequest validates entity_type is valid enum (Scenario: Create entity fails on invalid data)
- [ ] 2.1.15 [Unit] UpdateEntityRequest validates user_profile_id exists (Scenario: Edit an existing entity)

### 2.2 Address Association for Entities

- [ ] 2.2.1 [Feature] User can create entity with existing address (Scenario: Create entity with existing address)
- [ ] 2.2.2 [Feature] Entity created with address associates address_id correctly (Scenario: Create entity with existing address)
- [ ] 2.2.3 [Feature] User can create entity without address (address_id = NULL) (Scenario: Create entity without address)
- [ ] 2.2.4 [Feature] Clicking "Create new address" navigates to /management/addresses from entity form (Scenario: Navigate to address management from entity form)
- [ ] 2.2.5 [Feature] User can edit entity and change address (Scenario: Edit entity and change address)
- [ ] 2.2.6 [Feature] Entity address update reflects in database (Scenario: Edit entity and change address)

### 2.3 User Profile Selection for Entities

- [ ] 2.3.1 [Feature] Entity creation form shows available profiles in dropdown (Scenario: Create entity shows available profiles)
- [ ] 2.3.2 [Feature] Dropdown displays jurisdiction and tax_id for context (Scenario: Create entity shows available profiles)
- [ ] 2.3.3 [Feature] Entity creation fails if profile not selected (Scenario: Create entity requires profile selection)
- [ ] 2.3.4 [Feature] Entity creation displays "User profile is required" error (Scenario: Create entity requires profile selection)
- [ ] 2.3.5 [Unit] StoreEntityRequest validates user_profile_id is required (Scenario: Create entity requires profile selection)

### 2.4 Authorization for Entities

- [ ] 2.4.1 [Feature] Unauthenticated user is redirected when accessing /management/entities (Scenario: User can view own entity)
- [ ] 2.4.2 [Feature] User cannot view another user's entity (403 Forbidden) (Scenario: User cannot view another user's entity)
- [ ] 2.4.3 [Feature] User cannot edit another user's entity (403 Forbidden) (Scenario: User cannot view another user's entity)
- [ ] 2.4.4 [Feature] User cannot delete another user's entity (403 Forbidden) (Scenario: User cannot view another user's entity)
- [ ] 2.4.5 [Unit] EntityPolicy->view() returns true only for entity owner (Scenario: User cannot view another user's entity)
- [ ] 2.4.6 [Unit] EntityPolicy->update() returns true only for entity owner (Scenario: User cannot view another user's entity)
- [ ] 2.4.7 [Unit] EntityPolicy->delete() returns true only for entity owner (Scenario: User cannot view another user's entity)

### 2.5 Dependency Checks for Entities

- [ ] 2.5.1 [Feature] Entity page warns when no profiles exist (Scenario: Entity page warns when no profiles exist)
- [ ] 2.5.2 [Feature] "Create Entity" button is disabled when no profiles exist (Scenario: Entity page warns when no profiles exist)
- [ ] 2.5.3 [Feature] Entity creation is enabled when profiles exist (Scenario: Entity creation enabled when profiles exist)

---

## 3. Address Management

### 3.1 Address CRUD Operations

- [ ] 3.1.1 [Feature] User can list all their addresses (Scenario: List all user addresses)
- [ ] 3.1.2 [Feature] Address list displays street, city, state, postal_code, country, association_status, created_at (Scenario: List all user addresses)
- [ ] 3.1.3 [Feature] User can create a new address with valid data (Scenario: Create a new address)
- [ ] 3.1.4 [Feature] Address creation requires all fields (street, city, state, postal_code, country) (Scenario: Create a new address)
- [ ] 3.1.5 [Feature] Address creation fails with missing required fields (Scenario: Create address fails on invalid data)
- [ ] 3.1.6 [Feature] Address creation displays validation error messages (Scenario: Create address fails on invalid data)
- [ ] 3.1.7 [Feature] Address is created with user_id = current user (Scenario: Create a new address)
- [ ] 3.1.8 [Feature] User can edit an existing address (Scenario: Edit an existing address)
- [ ] 3.1.9 [Feature] Address edit updates all fields (street, city, state, postal_code, country) (Scenario: Edit an existing address)
- [ ] 3.1.10 [Feature] User can delete an address not associated to any models (Scenario: Delete an address)
- [ ] 3.1.11 [Feature] Address deletion is prevented if address is in use (Scenario: Delete an address)
- [ ] 3.1.12 [Feature] Address deletion displays error with model details when blocked (Scenario: Delete an address)
- [ ] 3.1.13 [Unit] StoreAddressRequest validates all required fields (Scenario: Create address fails on invalid data)
- [ ] 3.1.14 [Unit] UpdateAddressRequest validates country is string (ISO code) (Scenario: Edit an existing address)

### 3.2 Address Reusability

- [ ] 3.2.1 [Feature] Multiple models can reference the same address_id (Scenario: Address used by multiple models)
- [ ] 3.2.2 [Feature] Address list shows "Associated to: UserProfile #1, Entity #2" when used by multiple models (Scenario: Address used by multiple models)
- [ ] 3.2.3 [Feature] User can reassign address to a different model (Scenario: Address can be reassigned)
- [ ] 3.2.4 [Feature] Old address association is removed when reassigned (Scenario: Address can be reassigned)
- [ ] 3.2.5 [Feature] Address deletion is blocked if any model still uses it (Scenario: Prevent deletion of in-use address)
- [ ] 3.2.6 [Feature] Deletion error shows which model is using the address (Scenario: Prevent deletion of in-use address)

### 3.3 Address Ownership

- [ ] 3.3.1 [Feature] User can only list their own addresses (user_id = current user) (Scenario: User can manage own addresses)
- [ ] 3.3.2 [Feature] User cannot view another user's address (403 Forbidden) (Scenario: User cannot view another user's address)
- [ ] 3.3.3 [Feature] User cannot edit another user's address (403 Forbidden) (Scenario: User cannot view another user's address)
- [ ] 3.3.4 [Feature] User cannot delete another user's address (403 Forbidden) (Scenario: User cannot view another user's address)
- [ ] 3.3.5 [Unit] AddressPolicy->view() returns true only for address owner (Scenario: User cannot view another user's address)
- [ ] 3.3.6 [Unit] AddressPolicy->update() returns true only for address owner (Scenario: User cannot view another user's address)
- [ ] 3.3.7 [Unit] AddressPolicy->delete() returns true only for address owner (Scenario: User cannot view another user's address)

### 3.4 Address Structure

- [ ] 3.4.1 [Unit] Address can be associated to UserProfile (user_profile_id NOT NULL, others NULL) (Scenario: Address structure validation)
- [ ] 3.4.2 [Unit] Address can be associated to Entity (entity_id NOT NULL, others NULL) (Scenario: Address structure validation)
- [ ] 3.4.3 [Unit] Address can be unassociated (all FK fields NULL) (Scenario: Address structure validation)
- [ ] 3.4.4 [Feature] UI prevents associating address to multiple model types simultaneously in Fase 1.2 (Scenario: Address cannot be associated to multiple model types simultaneously)

### 3.5 Address Display in Profile/Entity Forms

- [ ] 3.5.1 [Feature] Profile/entity creation form displays address dropdown (Scenario: Address dropdown shows available addresses)
- [ ] 3.5.2 [Feature] Dropdown shows "(None)" as first option (Scenario: Address dropdown shows available addresses)
- [ ] 3.5.3 [Feature] Dropdown shows unassociated addresses as selectable (Scenario: Address dropdown shows available addresses)
- [ ] 3.5.4 [Feature] Dropdown shows associated addresses as disabled/grayed (Scenario: Address dropdown shows available addresses)
- [ ] 3.5.5 [Feature] User can leave address empty in profile/entity creation (Scenario: User can leave address empty)
- [ ] 3.5.6 [Feature] Profile/entity created without address has address_id = NULL (Scenario: User can leave address empty)

---

## 4. Form Validation & Authorization

### 4.1 Form Request Validation Classes

- [ ] 4.1.1 [Unit] StoreJurisdictionRequest validates correctly (migrated from inline validation) (Scenario: Jurisdiction validation migrated to Form Request)
- [ ] 4.1.2 [Unit] StoreUserProfileRequest validates user_id, jurisdiction_id, tax_id (Scenario: UserProfile validation via Form Request)
- [ ] 4.1.3 [Unit] UpdateUserProfileRequest validates jurisdiction_id exists (Scenario: UserProfile validation via Form Request)
- [ ] 4.1.4 [Unit] StoreEntityRequest validates user_profile_id, name, entity_type, tax_id (Scenario: Entity validation via Form Request)
- [ ] 4.1.5 [Unit] UpdateEntityRequest validates entity_type is valid EntityType (Scenario: Entity validation via Form Request)
- [ ] 4.1.6 [Unit] StoreAddressRequest validates all address fields (street, city, state, postal_code, country) (Scenario: Address validation via Form Request)
- [ ] 4.1.7 [Unit] UpdateAddressRequest validates all address fields (Scenario: Address validation via Form Request)
- [ ] 4.1.8 [Feature] Form validation errors are displayed in Livewire component (Scenario: Validation error display)
- [ ] 4.1.9 [Feature] Same Form Request is used by API endpoint and UI (Scenario: Form Request reusable for API and UI)

### 4.2 Custom Error Messages

- [ ] 4.2.1 [Unit] StoreUserProfileRequest has custom message for required jurisdiction (Scenario: Custom message for required field)
- [ ] 4.2.2 [Unit] StoreUserProfileRequest has custom message for duplicate tax_id (Scenario: Custom message for unique constraint)
- [ ] 4.2.3 [Unit] StoreEntityRequest has custom message for invalid entity_type (Scenario: Custom message for enum validation)
- [ ] 4.2.4 [Feature] Form displays custom error messages (not generic Laravel messages) (Scenario: Custom message for required field)

### 4.3 Authorization Gates in Form Requests

- [ ] 4.3.1 [Unit] Unauthenticated request fails StoreUserProfileRequest::authorize() (Scenario: Non-authenticated user cannot create)
- [ ] 4.3.2 [Unit] Unauthenticated request fails StoreEntityRequest::authorize() (Scenario: Non-authenticated user cannot create)
- [ ] 4.3.3 [Unit] Unauthenticated request fails StoreAddressRequest::authorize() (Scenario: Non-authenticated user cannot create)
- [ ] 4.3.4 [Unit] UpdateUserProfileRequest::authorize() checks user ownership (Scenario: Owner-only authorization for updates)
- [ ] 4.3.5 [Unit] UpdateEntityRequest::authorize() checks user ownership (Scenario: Owner-only authorization for updates)
- [ ] 4.3.6 [Unit] UpdateAddressRequest::authorize() checks user ownership (Scenario: Owner-only authorization for updates)
- [ ] 4.3.7 [Feature] Unauthorized update request returns 403 Forbidden (Scenario: Authorization check prevents unauthorized access)

---

## Summary

**Total Tests: ~120 tests**
- Feature tests: ~70
- Unit tests: ~40
- Browser tests: 0 (can add later for UI flow testing)

**Coverage Areas:**
- CRUD operations (create, read, update, delete)
- Validation (required fields, unique constraints, enums)
- Authorization (ownership checks, policies)
- Relationships (address reusability, dependencies)
- Error handling (validation messages, deletion prevention)
- Form Requests (validation, custom messages, authorization gates)
