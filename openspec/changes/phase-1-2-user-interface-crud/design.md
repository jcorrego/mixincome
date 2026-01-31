# Design: Phase 1.2 — User Interface for UserProfile, Entity, Address CRUD

## Context

Fase 1.1 completed 151 passing tests validating UserProfile, Entity, and Address models. These models exist only in code and tests. Fase 1.2 builds the operational UI layer to allow users to actually manage their tax profiles, entities, and addresses before moving to financial tracking (Fase 2).

**Current state:**
- UserProfile, Entity, Address models exist with relationships
- Address uses polymorphic `morphs()` relationship (currently designed but not optimal for reuse)
- Jurisdictions CRUD exists with Livewire monolithic component + inline validation
- No controllers, form requests, or policies yet
- Authorization is implicit (no explicit policies)

**Stakeholders:** Single user (MVP), but authorization architecture should be designed for multi-user future.

---

## Goals / Non-Goals

**Goals:**
- Create operational CRUD interfaces for UserProfile, Entity, Address (independent views, not nested)
- Restructure Address from polymorphic to simple FK to enable reuse across multiple models
- Introduce Form Request validation and Authorization Policies as architectural patterns
- Provide dependency checks and soft warnings (users guided to create parents first)
- Validate models work in real UI before scaling to Finance Schema
- Establish routing + Livewire + API endpoint patterns for consistency in future phases

**Non-Goals:**
- Bulk operations or batch management (single-item CRUD only)
- Complex permission hierarchies (simple user-owns-data for MVP)
- Address change history or auditing
- Address geocoding or validation against external services
- API documentation or public API (internal use only at this stage)
- Mobile optimization (desktop-first, responsive but not mobile-specific)

---

## Decisions

### 1. Address Structure: Simple FK (not Polymorphic)

**Decision:** Change Address from polymorphic (`morphs()`) to simple foreign keys. Address becomes an independent reusable resource owned by the user.

```php
// CURRENT (Fase 1.1):
Address: addressable_id, addressable_type (polymorphic)

// NEW (Fase 1.2):
Address: user_id (FK to users), street, city, state, postal_code, country
UserProfile: address_id (FK to addresses, nullable)
Entity: address_id (FK to addresses, nullable)
Account: address_id (FK to addresses, nullable) — Fase 2
Asset: address_id (FK to addresses, nullable) — Fase 2
```

**Rationale:**
- Address is an independent resource that multiple models can reference
- Multiple models can share the same Address (e.g., UserProfile#1 and Entity#2 both point to address_id=5)
- Avoids creating duplicate addresses for the same location
- Simpler queries and relationships (BelongsTo from UserProfile/Entity to Address)
- User-owned addresses (address.user_id) enable access control
- Aligns with future Accounts/Assets (Fase 2) which also need addresses

**Alternatives considered:**
- Keep polymorphic: Forces separate address per model, requires duplicates
- Junction table (AddressAssignment): Over-engineered for current MVP scope
- **CHOSEN**: Simple FK on each model (chosen)

**Breaking change:** Yes — existing addresses must be restructured. Rollback plan: keep old migration, create new one that drops polymorphic columns and adds FK columns.

---

### 2. Three Independent Views (not Nested Routes)

**Decision:** `/management/profiles`, `/management/entities`, `/management/addresses` as independent Livewire components (flat, not nested).

**Rationale:**
- Matches existing Jurisdictions pattern (single-page Livewire component)
- Avoids nested routes complexity (no `/profiles/{id}/entities/{id}`)
- Simpler state management (one component = one model CRUD)
- Dependency checks display warnings when parents don't exist (soft UX)

**Alternatives considered:**
- Nested routes (Profiles → Entities under profile): More complex routing, deeper state coupling
- Separate Volt pages per action: More files, more boilerplate
- **CHOSEN**: Independent Livewire components (chosen)

---

### 3. Monolithic Livewire Components (like Jurisdictions)

**Decision:** Each model gets ONE Livewire component that handles list, create, edit, delete, modals.

```
ProfilesComponent:
  - List + table with actions
  - Create modal
  - Edit modal
  - Confirmation for delete

EntityComponent:
  - Same pattern
  - Requires UserProfile selection

AddressComponent:
  - Same pattern
  - Shows association status (which model uses this address)
```

**Rationale:**
- Consistent with existing Jurisdictions (proven pattern in codebase)
- Less boilerplate than separate components per action
- State stays in one place (easier to debug)
- Livewire can dispatch modals efficiently

**Alternatives considered:**
- Separate components (List, Create, Edit, Delete): More DRY, more files
- **CHOSEN**: Monolithic (chosen)

---

### 4. API Endpoints + Livewire Dispatch

**Decision:** Livewire components dispatch POST/PATCH/DELETE to API endpoints; controllers handle validation + authorization.

```
Livewire component:
  → wire:click="delete(5)"
    → $this->dispatch('api:delete', id: 5)
    → POST /api/management/profiles/5/delete

Controller:
  → Validate via Form Request
  → Check Authorization Policy
  → Perform action
  → Return response
```

**Rationale:**
- Separates UI logic from business logic
- Enables Form Request validation and Policies (both critical)
- Prepares for future API-driven frontend (Inertia, Vue, etc.)
- Matches Laravel best practices

**Alternatives considered:**
- Inline Livewire methods (no API): Simpler, less separation, harder to test
- **CHOSEN**: API endpoints (chosen)

---

### 5. Form Requests for Validation

**Decision:** Create Form Request classes for each model action (Store, Update) instead of inline validation.

```
StoreUserProfileRequest
  - rules(): user_id (owned), jurisdiction_id (exists), tax_id (unique per profile)
  - authorize()
  - messages()

UpdateUserProfileRequest
  - Similar

StoreEntityRequest
  - entity_type must be valid EntityType
  - user_profile_id required
```

**Rationale:**
- Reusable validation logic (used by API + potential batch operations)
- Type-safe (return type hints for validated data)
- Custom error messages
- Explicit authorization gate
- Better testing

**Alternatives considered:**
- Inline validation in controller: Less reusable, harder to test
- **CHOSEN**: Form Requests (chosen)

**Also:** Migrate Jurisdictions to Form Requests for consistency.

---

### 6. Authorization Policies

**Decision:** Create Policy classes (UserProfilePolicy, EntityPolicy, AddressPolicy) with explicit authorization checks.

```php
UserProfilePolicy:
  - viewAny(User): true (user sees their own)
  - view(User, Profile): owner check
  - create(User): true
  - update(User, Profile): owner check
  - delete(User, Profile): owner check + no entities constraint

EntityPolicy:
  - delete(User, Entity): owner check + no filings constraint (future)
```

**Rationale:**
- Centralized authorization logic
- Reusable across controllers, policies, scopes
- Explicit row-level access control
- Prepared for future multi-user or role-based features

**Alternatives considered:**
- Inline checks in controller: Scattered, hard to maintain
- **CHOSEN**: Policies (chosen)

---

### 7. Address Selector in Forms (not Inline Creation)

**Decision:** When creating Profile/Entity, user selects existing address from dropdown OR navigates to create new (doesn't create inline).

**Rationale:**
- Address is now independent, not model-specific
- Avoids modal-within-modal complexity
- User can batch-create addresses first, then use them
- Consistent with "separate concerns" principle

**Alternatives considered:**
- Inline address creation in modal: Adds complexity, nested modals
- **CHOSEN**: Address selector + separate creation flow (chosen)

---

## Risks / Trade-offs

| Risk | Mitigation |
|------|-----------|
| Address FK migration breaks existing data | Rollback plan: keep old migration, create new. User handles in production (data ops task). |
| Monolithic Livewire components grow large | If component exceeds ~400 lines, extract sub-components. Not a problem now. |
| No nested routes = harder to enforce "Entity belongs to Profile" in UI flow | Dependency warnings + policy checks + tests verify constraints. UX: list warns if no profile. |
| Address reuse across models may cause confusion | Show association clearly in list. Document in specs. |
| Single-user authorization (no multi-tenancy) limits future scaling | Policies designed for future role-based access; tests enforce user isolation. |

---

## Migration Plan

### Phase 1: Database Migration
1. Edit existing `create_addresses_table` migration (from Fase 1.1)
   - Remove: `addressable_id`, `addressable_type` (polymorphic columns)
   - Ensure table has: `id`, `user_id` (FK to users), `street`, `city`, `state`, `postal_code`, `country`, `created_at`, `updated_at`

2. Edit `create_user_profiles_table` migration to add `address_id` FK
   - Add: `address_id` (nullable FK to addresses.id)

3. Edit `create_entities_table` migration to add `address_id` FK
   - Add: `address_id` (nullable FK to addresses.id)

4. Run migrations fresh:
   - Rollback: `php artisan migrate:rollback`
   - Rollback plan: existing migrations are edited, so rollback reverts them naturally

### Phase 2: Code Rollout
1. Update models (UserProfile, Entity) → add `address_id` FK, relationships
2. Create controllers, Form Requests, Policies
3. Create Livewire components, views
4. Update routes
5. Add tests
6. Run `composer lint` and `php artisan test`

### Rollback Strategy
- Revert migrations (Laravel rollback)
- Revert code changes (git revert)
- Tested locally before production deployment

---

## Open Questions

1. **Address hard delete:** When deleting an Address used by multiple models, should we prevent (foreign key constraint) or cascade? → **Decision pending:** Recommend prevent (foreign key) to protect data integrity.

3. **Dependency notifications:** Should warnings be dismissible/sticky or always visible? → **Decision pending:** Always visible (soft requirement).

4. **Future: Address versioning:** If we need to track address history, should we add `is_current` flag or separate table? → **Out of scope:** Defer to future phase.
