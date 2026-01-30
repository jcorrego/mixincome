# Design: Phase 1.1 — User Profiles, Entities & Addresses

## Context

MixIncome is a multi-jurisdiction tax assistant. Users need to manage:
- Presence in multiple countries (e.g., individual in Spain + USA + Colombia)
- One legal identity per jurisdiction (via UserProfile)
- Additional legal entities per jurisdiction (LLC, S-Corp, etc.)
- Shared addresses across entities and models (single address, multiple "owners")

Current state:
- `User` model exists (Fortify-managed, auth-focused)
- `Jurisdiction` model exists (Spain, USA, Colombia)
- No domain model for tax profiles or legal entities
- No address system

This design establishes the foundation for all future data models (Fase 2+: Finance, Accounts, Transactions, etc.).

## Goals / Non-Goals

**Goals:**
1. Model user presence in multiple jurisdictions (UserProfile: 1 user + 1 jurisdiction = 1 profile)
2. Support additional legal entities per jurisdiction (Entity: LLC, S-Corp, Partnership, etc.)
3. Implement polymorphic, reusable addresses (owned by User, associated with multiple models)
4. Establish clear ownership hierarchy (User → UserProfile → Entity/Accounts/Assets)
5. Maintain 100% type coverage and strict Laravel patterns
6. Support future Fase 2+ models without schema changes

**Non-Goals:**
- User address management (User.address, if needed, is separate from Entity addresses)
- ResidencyPeriod or tax residency tracking (defer to Fase 1.2 or later if needed)
- Address validation against external APIs
- Multi-tenant or org-level isolation (single-user MVP)
- Role-based access control (defer to Auth Fase)

## Decisions

### Decision 1: UserProfile as "Tax Legal Person" Container

**Choice**: UserProfile = (User + Jurisdiction) tuple, NOT a generic "profile" with preferences

**Rationale**:
- Each jurisdiction has different tax ID formats, tax years, requirements
- One user can have multiple UserProfiles (one per jurisdiction)
- UserProfile is the "tax identity" — the legal person filing taxes in that country
- Simpler than embedding jurisdiction into User or creating separate structures

**Alternatives Considered**:
1. **Single User with jurisdiction array**: Harder to enforce 1:1 mapping per juris. Address ambiguity.
2. **Subclass User per jurisdiction**: Over-engineered; violates Laravel conventions.
3. **UserProfile as "settings" only**: Insufficient — we need a clear legal container for Entity ownership.

**Schema**:
```php
UserProfile:
  user_id (FK User)
  jurisdiction_id (FK Jurisdiction) — UNIQUE pair
  tax_id (string) — SSN, RUT, NIF, etc.
  status (string) — Active, Inactive
```

---

### Decision 2: Entity = Legal Entities ONLY (No Individual)

**Choice**: Entity model is for LLC, S-Corp, Partnership, Trust — NOT for Individual

**Rationale**:
- Individual taxes are handled by UserProfile directly
- Entity is only for "additional legal persons" a user owns
- Simpler mental model: User ≡ Individual, Entity ≡ Company
- Avoids confusion of "multiple individuals" (which doesn't exist)

**Example**:
```
Juan (User):
├─ UserProfile(Spain): tax_id=NIF123, status=Active
│  └─ Entity(LLC Spain): "My Tech LLC"
│
└─ UserProfile(USA): tax_id=SSN456, status=Active
   └─ Entity(S-Corp USA): "Juan's Consulting Corp"
```

Juan doesn't create another "Individual" Entity; the UserProfile IS his individual presence.

**Implications**:
- Entity `entity_type` enum: LLC, SCorp, CCorp, Partnership, Trust, Other (NO Individual)
- Account ownership: `user_profile_id` (always) + optional `entity_id` (null = user account)
- Asset ownership: same pattern as Account

---

### Decision 3: Address as Polymorphic, User-Owned, Reusable

**Choice**: Address is polymorphic (morphTo), but with explicit `user_id` (owner)

**Rationale**:
- Multiple models need addresses (UserProfile, Entity, Account, Asset)
- Same address can be associated with multiple "owners" of same user (e.g., LLC address = UserProfile address)
- User ownership (`user_id`) enforces privacy/authorization boundary
- Simpler than separate address tables per model; avoids data duplication

**Alternative Considered**: Address with `is_primary` flag per model
- Problem: If LLC and User share same address, who's "primary"? Ambiguous.
- Solution: No type/flag. Each model has one address (the "official" one).

**Schema**:
```php
Address:
  addressable_id (int)
  addressable_type (string) — 'App\Models\UserProfile', 'App\Models\Entity', etc.
  user_id (FK User) — Owner (authorization boundary)
  street, city, state, postal_code, country
  timestamps

Relationships:
  morphTo('addressable') — UserProfile, Entity, Account, Asset
  belongsTo(User) — Via user_id for ownership
```

**Future Expansion**:
- When Account/Asset are created (Fase 2), they get `polymorphic address` automatically
- No schema changes needed

---

### Decision 4: Eager Loading & N+1 Prevention

**Choice**: Define eager loading relationships at model level; use `load()` in factories/tests

**Rationale**:
- Strict models (via nunomaduro/essentials) enforce auto eager loading
- Factory relationships are explicit (e.g., `has(Address)` or manually associated)
- Tests verify no N+1 via Pest assertions

**Implementation**:
```php
// UserProfile.php
public function address(): MorphOne
{
    return $this->morphOne(Address::class, 'addressable');
}

public function entity(): HasMany
{
    return $this->hasMany(Entity::class);
}

// Entity.php
public function address(): MorphOne
{
    return $this->morphOne(Address::class, 'addressable');
}

// Address.php
public function addressable(): MorphTo
{
    return $this->morphTo();
}

public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

---

### Decision 5: Enum Placement & Naming

**Choice**: Single `EntityType` enum; TitleCase keys (LLC, SCorp, CCorp, Partnership, Trust, Other)

**Rationale**:
- Follows project convention (Enum keys in TitleCase)
- Reduces enum proliferation (only EntityType needed for 1.1)
- Extensible if needed in future (e.g., add "Foundation", "Cooperative")

**Location**: `app/Enums/EntityType.php`

---

### Decision 6: Factory & Seeder Strategy

**Choice**: Create factories for all models; minimal seeder (only for dev/demo)

**Rationale**:
- Factories support test data generation (realistic, fast)
- Seeders optional — mostly for local development showcase
- Tests use factories directly (DRY, no seeder overhead)

**Implementation**:
```php
// Factories
UserProfileFactory: jurisdiction=random, tax_id=realistic, status=Active
EntityFactory: entity_type=random, name="Fake Company", tax_id=EIN-like
AddressFactory: addressable=null (explicit in test), user_id=required

// Seeders (optional)
DatabaseSeeder calls UserProfileSeeder (creates demo user with 2-3 profiles)
```

---

### Decision 7: Validation & Casts

**Choice**: Use Form Request validation (for future UI); model casts for type safety

**Rationale**:
- 100% type coverage requires explicit casts
- Form Requests for API/Web endpoints (deferred to Fase 4 - UI)
- Model relationships auto-load (strict models)

**Casts**:
```php
UserProfile:
  user_id => int
  jurisdiction_id => int
  status => string (enum when Fase 2 needs enum)

Entity:
  user_profile_id => int
  entity_type => EntityType (native enum cast)
  status => string

Address:
  addressable_id => int
  user_id => int
  (other fields => string, no special casts)
```

---

## Risks / Trade-offs

### Risk 1: Address Polymorphism Complexity
**Risk**: Polymorphic relationships are harder to query/optimize than foreign keys.
**Mitigation**:
- Tests verify no N+1 queries
- Use explicit `with()` in controllers/services (Fase 4)
- Consider adding `address_id` denormalization later if performance needed

### Risk 2: UserProfile Uniqueness Not Enforced at DB Level
**Risk**: If app logic fails, duplicate (user_id, jurisdiction_id) pairs could exist.
**Mitigation**:
- Migration includes unique index: `unique(['user_id', 'jurisdiction_id'])`
- Model factory ensures uniqueness
- Tests verify constraint

### Risk 3: Entity Always Requires UserProfile
**Risk**: No "orphaned" entities allowed; if profile deleted, entities cascade-delete.
**Mitigation**:
- This is intended behavior (E-commerce company doesn't exist without its owner)
- Migration uses `onDelete('cascade')`
- Document in migration comment

### Risk 4: Address Owner Enforcement Not at DB Level
**Risk**: Address can be associated with models of different users.
**Mitigation**:
- Model relationships enforce `user_id` implicitly (addressable belongsTo User indirectly)
- Validation/authorization deferred to controllers (Fase 4)
- For now, assume trusted environment (single-user MVP)

---

## Migration Plan

### Steps:
1. Create Enum: `EntityType`
2. Create Migration: `create_user_profiles_table`
3. Create Migration: `create_entities_table`
4. Create Migration: `create_addresses_table`
5. Create Models: UserProfile, Entity, Address (with relationships)
6. Create Factories: UserProfileFactory, EntityFactory, AddressFactory
7. Write Tests: relationships, polymorphism, no N+1

### Rollback:
- Drop tables in reverse order: `addresses`, `entities`, `user_profiles`
- Delete models and factories

---

## Open Questions

1. **Should Address support "soft deletes"?**
   - Deferred to future if audit trail needed
   - For now: hard delete

2. **Should UserProfile have a "primary" flag?**
   - Deferred to UI phase (Fase 4) if UX needs default jurisdiction
   - For now: not needed

3. **Account & Asset polymorphism for address (Fase 2)?**
   - Assume they will use same polymorphic pattern
   - No schema changes needed now

4. **Audit/versioning for tax_id changes?**
   - Deferred; for now, treat as immutable (UI prevents changes)
   - Could add with Laravel Auditing package later

---
