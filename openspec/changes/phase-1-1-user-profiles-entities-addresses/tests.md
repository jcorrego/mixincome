# Tests: Phase 1.1 — User Profiles, Entities & Addresses

TDD Red phase: tests defining required behavior. All must FAIL until implementation complete.

---

## 1. User Profile Creation

- [x] 1.1 [Unit] Can create UserProfile with user_id, jurisdiction_id, and tax_id
- [x] 1.2 [Unit] UserProfile status defaults to "Active"
- [x] 1.3 [Unit] Timestamps (created_at, updated_at) are auto-generated
- [x] 1.4 [Unit] Unique constraint (user_id, jurisdiction_id) prevents duplicates
- [x] 1.5 [Feature] POST /profiles creates new UserProfile with valid data
- [x] 1.6 [Feature] Duplicate (user_id, jurisdiction_id) pair returns validation error

---

## 2. User Profile Retrieval

- [x] 2.1 [Unit] Can retrieve UserProfile by ID
- [x] 2.2 [Unit] UserProfile has eager-loaded relationship to Jurisdiction
- [x] 2.3 [Feature] GET /profiles/:id returns UserProfile with jurisdiction details
- [x] 2.4 [Feature] GET /users/:id/profiles lists all profiles for a user
- [x] 2.5 [Unit] No N+1 queries when loading profiles with jurisdiction

---

## 3. User Profile Updates

- [x] 3.1 [Unit] Can update UserProfile.tax_id without creating duplicate profile
- [x] 3.2 [Unit] Can update UserProfile.status (Active → Inactive)
- [x] 3.3 [Feature] PUT /profiles/:id updates tax_id successfully
- [x] 3.4 [Feature] Updated_at timestamp changes on update

---

## 4. User Profile Deletion

- [x] 4.1 [Unit] Deleting UserProfile cascades to related Entities
- [x] 4.2 [Unit] Deleting UserProfile without entities works cleanly
- [x] 4.3 [Feature] DELETE /profiles/:id removes profile and related entities

---

## 5. User Profile-Jurisdiction Relationship

- [x] 5.1 [Unit] UserProfile.jurisdiction returns correct Jurisdiction model
- [x] 5.2 [Unit] Jurisdiction.default_currency is accessible via profile
- [x] 5.3 [Unit] Changing jurisdiction_id updates profile's jurisdiction

---

## 6. Entity Creation

- [x] 6.1 [Unit] Can create Entity with user_profile_id, name, entity_type, tax_id
- [x] 6.2 [Unit] Entity.entity_type is cast to EntityType enum
- [x] 6.3 [Unit] Entity status defaults to "Active"
- [x] 6.4 [Unit] Cannot create Entity with entity_type="Individual" (enum rejects it)
- [x] 6.5 [Unit] Multiple entities can exist under same UserProfile
- [x] 6.6 [Feature] POST /profiles/:id/entities creates entity under profile
- [x] 6.7 [Feature] Creating UserProfile does NOT auto-create entities

---

## 7. Entity Retrieval

- [x] 7.1 [Unit] Can retrieve Entity by ID
- [x] 7.2 [Unit] Entity has eager-loaded relationship to UserProfile
- [x] 7.3 [Unit] UserProfile.entities returns all entities under profile
- [x] 7.4 [Feature] GET /entities/:id returns Entity with profile context
- [x] 7.5 [Feature] GET /profiles/:id/entities lists entities for profile
- [x] 7.6 [Unit] No N+1 queries when loading entities with profile

---

## 8. Entity Updates

- [x] 8.1 [Unit] Can update Entity.name
- [x] 8.2 [Unit] Can update Entity.status (Active → Inactive)
- [x] 8.3 [Unit] Can update Entity.tax_id
- [x] 8.4 [Feature] PUT /entities/:id updates entity fields
- [x] 8.5 [Unit] Updated_at timestamp changes on update

---

## 9. Entity Deletion

- [x] 9.1 [Unit] Deleting Entity cascades to related Accounts (Fase 2)
- [x] 9.2 [Unit] Deleting Entity cascades to related Address if exists
- [x] 9.3 [Feature] DELETE /entities/:id removes entity
- [x] 9.4 [Unit] Deleting Entity does NOT cascade to UserProfile (parent not affected)

---

## 10. Entity-UserProfile Relationship

- [x] 10.1 [Unit] Entity.userProfile returns correct UserProfile
- [x] 10.2 [Unit] Entity inherits jurisdiction via Entity.userProfile.jurisdiction
- [x] 10.3 [Unit] Cannot change entity's user_profile_id after creation (immutable parent)

---

## 11. Entity Types (Enum)

- [x] 11.1 [Unit] EntityType enum contains: LLC, SCorp, CCorp, Partnership, Trust, Other
- [x] 11.2 [Unit] EntityType enum does NOT contain Individual
- [x] 11.3 [Unit] Entity.entity_type is type-safe (enum, not string)
- [x] 11.4 [Unit] Creating Entity with invalid type rejects request

---

## 12. Entity Tax ID Requirement

- [x] 12.1 [Unit] tax_id is required when creating Entity
- [x] 12.2 [Feature] POST /entities without tax_id returns validation error

---

## 13. Address Creation

- [x] 13.1 [Unit] Can create Address with street, city, state, postal_code, country
- [x] 13.2 [Unit] Address is polymorphic (addressable_type, addressable_id)
- [x] 13.3 [Unit] Address has user_id (owner) for authorization
- [x] 13.4 [Unit] Address country is required (validation fails without it)
- [x] 13.5 [Unit] Can associate Address with UserProfile (polymorphic)
- [x] 13.6 [Unit] Can associate Address with Entity (polymorphic)
- [x] 13.7 [Feature] POST /addresses creates address and associates to addressable
- [x] 13.8 [Feature] Validation requires street, city, state, postal_code, country

---

## 14. Address Reuse

- [x] 14.1 [Unit] Same Address row can be referenced by UserProfile and Entity
- [x] 14.2 [Unit] User.addresses returns all addresses owned by user (inverse polymorphic)
- [x] 14.3 [Feature] GET /users/:id/addresses lists all addresses for user
- [x] 14.4 [Unit] Reusing address avoids data duplication

---

## 15. Address Retrieval

- [x] 15.1 [Unit] Can retrieve Address by ID
- [x] 15.2 [Unit] UserProfile.address returns associated Address (morphOne)
- [x] 15.3 [Unit] Entity.address returns associated Address (morphOne)
- [x] 15.4 [Unit] Address.addressable returns correct polymorphic model
- [x] 15.5 [Feature] GET /addresses/:id returns Address with addressable context
- [x] 15.6 [Unit] No N+1 queries when loading addresses with relationships

---

## 16. Address Updates

- [x] 16.1 [Unit] Can update Address.street, city, state, postal_code, country
- [x] 16.2 [Feature] PUT /addresses/:id updates address fields
- [x] 16.3 [Unit] Updated_at timestamp changes on update

---

## 17. Address Deletion

- [x] 17.1 [Unit] Can delete Address
- [x] 17.2 [Feature] DELETE /addresses/:id removes address
- [x] 17.3 [Unit] Deleting Entity with address deletes the address (cascade)

---

## 18. Address Polymorphism

- [x] 18.1 [Unit] Address.addressable_type can be 'App\Models\UserProfile'
- [x] 18.2 [Unit] Address.addressable_type can be 'App\Models\Entity'
- [x] 18.3 [Unit] Address.addressable_type can be 'App\Models\Account' (Fase 2+)
- [x] 18.4 [Unit] Address.addressable_type can be 'App\Models\Asset' (Fase 2+)
- [x] 18.5 [Unit] Address.addressable returns actual model (UserProfile, Entity, etc.)

---

## 19. Address User Ownership

- [x] 19.1 [Unit] Address.user_id identifies owner
- [x] 19.2 [Unit] All user addresses have same user_id
- [x] 19.3 [Feature] GET /addresses/:id respects user ownership (auth boundary)

---

## 20. Database Constraints & Indexes

- [x] 20.1 [Unit] Migration creates user_profiles table with correct columns
- [x] 20.2 [Unit] Migration creates unique index on (user_id, jurisdiction_id)
- [x] 20.3 [Unit] Migration creates entities table with correct columns
- [x] 20.4 [Unit] Migration creates addresses table with polymorphic columns
- [x] 20.5 [Unit] Foreign keys are correctly configured (cascade deletes)

---

## 21. Factories & Realistic Data

- [x] 21.1 [Unit] UserProfileFactory generates realistic profile with valid jurisdiction
- [x] 21.1 [Unit] UserProfileFactory generates jurisdiction-specific tax_id formats
- [x] 21.3 [Unit] EntityFactory generates valid EntityType enum
- [x] 21.4 [Unit] EntityFactory generates realistic EIN-like tax_id
- [x] 21.5 [Unit] AddressFactory generates realistic street, city, state, country
- [x] 21.6 [Unit] Factories can be chained (UserProfile → Entities → Addresses)

---

## 22. Type Coverage

- [x] 22.1 [Unit] All model properties have explicit type declarations
- [x] 22.2 [Unit] All relationships have return type hints
- [x] 22.3 [Unit] All casts are explicitly typed
- [x] 22.4 [Unit] 100% type coverage enforced by Pest plugin

---

## 23. Model Relationships - Complete Hierarchy

- [x] 23.1 [Unit] User.userProfiles returns hasMany(UserProfile)
- [x] 23.2 [Unit] UserProfile.user returns belongsTo(User)
- [x] 23.3 [Unit] UserProfile.jurisdiction returns belongsTo(Jurisdiction)
- [x] 23.4 [Unit] UserProfile.entities returns hasMany(Entity)
- [x] 23.5 [Unit] UserProfile.address returns morphOne(Address)
- [x] 23.6 [Unit] Entity.userProfile returns belongsTo(UserProfile)
- [x] 23.7 [Unit] Entity.address returns morphOne(Address)
- [x] 23.8 [Unit] Address.addressable returns morphTo()
- [x] 23.9 [Unit] Address.user returns belongsTo(User)

---

## 24. Integration Tests (End-to-End)

- [x] 24.1 [Feature] Can create User → UserProfile → Entity → Address in sequence
- [x] 24.2 [Feature] Full data flow: User has 2 profiles (Spain, USA), Spain has 1 entity with address, USA has 2 entities
- [x] 24.3 [Feature] Deleting profile cascades correctly to entities and addresses
- [x] 24.4 [Feature] Querying full hierarchy with eager loading is efficient (no N+1)

---

## 25. Enum Tests

- [x] 25.1 [Unit] EntityType enum can be instantiated with LLC
- [x] 25.2 [Unit] EntityType enum can be instantiated with SCorp
- [x] 25.3 [Unit] EntityType enum cannot be instantiated with Individual (throws error or validation fails)
- [x] 25.4 [Unit] EntityType.cases() returns all valid types

---
