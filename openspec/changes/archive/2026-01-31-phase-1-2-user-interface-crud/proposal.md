# Proposal: Phase 1.2 — User Interface for UserProfile, Entity, Address CRUD

## Why

Fase 1.1 established the core domain models (UserProfile, Entity, Address) with 151 passing tests, but they exist only in code. Before scaling to Finance Schema (Fase 2), we need to validate these models in a real user interface and provide operational controls for users to manage their tax profiles, legal entities, and addresses. This phase bridges the gap between models and Finance Schema by creating the foundational UI layer.

## What Changes

### Core Additions
- **3 independent Livewire management pages** (`/management/profiles`, `/management/entities`, `/management/addresses`)
- **Address restructuring**: Change from polymorphic (`morphs()`) to simple foreign keys. Address becomes independent, reutilizable by multiple models (UserProfile, Entity, Account, Asset).
- **API endpoints** for CRUD operations (Livewire dispatches POST/PATCH/DELETE to `/api/...` endpoints)
- **Form Request validation** for all models (introduce custom validation classes, migrate Jurisdictions to Form Requests)
- **Authorization Policies** for row-level access control (user can only manage own data)
- **Dependency checks** — pages display soft warnings when parent models don't exist (e.g., "Create a UserProfile first" when no profiles exist, with disabled Create buttons)

### Database Changes
- **BREAKING**: Address migration changes from polymorphic to simple FK structure:
  - Remove: `addressable_id`, `addressable_type` columns
  - Add: `address_id` FK to UserProfile, Entity, Account, Asset models
  - Address table becomes: `id, user_id (owner), street, city, state, postal_code, country, timestamps`
- Existing migrations (`create_user_profiles_table`, `create_entities_table`, `create_addresses_table`) are re-edited to reflect the new structure

### UI/UX
- Flux UI Free components for all forms and tables
- Tailwind CSS v4 responsive layout
- Address selector in Profile/Entity forms (dropdown existing + link to create new)
- Address list shows association status (e.g., "Associated to: UserProfile #5" or "(Unassociated)")
- Form validation with error messages
- Modal-based CRUD (consistent with existing Jurisdictions pattern)

## Capabilities

### New Capabilities
- `user-profile-management`: CRUD interface for tax profiles (UserProfile)
  - Create/read/update/delete tax profiles
  - Associate addresses to profiles
  - Validate jurisdiction + tax_id uniqueness per user

- `entity-management`: CRUD interface for legal entities (Entity)
  - Create/read/update/delete entities
  - Require UserProfile selection
  - Associate addresses to entities
  - Dependency check: warn if no profiles exist

- `address-management`: Independent address CRUD
  - Create/read/update/delete addresses (unassociated or assigned to models)
  - Show association status in list
  - Reuse addresses across multiple models
  - Owner validation (user can only manage own addresses)

### Modified Capabilities
- `form-validation`: Add Form Request classes for validation (was inline in components)
  - New: StoreUserProfileRequest, UpdateUserProfileRequest
  - New: StoreEntityRequest, UpdateEntityRequest
  - New: StoreAddressRequest, UpdateAddressRequest
  - Migrate: Jurisdictions CRUD to Form Requests (existing component remains, validation moves to request)

## Impact

### Code Changes
- **Migrations**: Edit existing (1.1) migrations; rollback/refresh database
- **Models**: Add `address_id` FK to UserProfile, Entity (Account, Asset come in Fase 2)
- **Controllers**: Create UserProfileController, EntityController, AddressController (API endpoints)
- **Livewire Components**: Create Profiles, Entities, Addresses components (monolithic list+crud per model)
- **Views**: Create management layouts and blade templates
- **Policies**: Create UserProfilePolicy, EntityPolicy, AddressPolicy
- **Form Requests**: Create 6 form request classes (3 models × store/update)
- **Routes**: Add `/management/profiles`, `/management/entities`, `/management/addresses` routes and API endpoints

### Dependencies
- Models from Fase 1.1 (UserProfile, Entity, Address, Jurisdiction, User, EntityType enum)
- No new composer/npm dependencies

### Database
- **Breaking change**: Address polymorphic structure → simple FK
- Data migration: existing addresses must be re-associated or backfilled

### Testing
- Feature tests for CRUD routes (create, read, update, delete)
- Component tests for Livewire components
- Authorization tests (user isolation, policy enforcement)
- Form Request validation tests
- ~80-120 new tests

### Next Steps (Blocked by this phase)
- Fase 2: Finance Schema (Currencies, FxRates, Accounts, Transactions) — blocked until Address structure is stabilized
- Fase 3: Tax Reporting — blocked until Address is operational
